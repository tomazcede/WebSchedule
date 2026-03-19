<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use phpDocumentor\Reflection\Types\Integer;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\RecurrenceFrequency;
use Spatie\IcalendarGenerator\ValueObjects\RRule;

class ScheduleService
{
    public Schedule | null $schedule;

    public function __construct($val)
    {
        $this->schedule = match($val) {
            $val instanceof Schedule => $val,
            $val instanceof Integer => Schedule::find($val),
            default => self::convertFromJson($val),
        };
    }

    public static function convertFromJson($scheduleJson) : Schedule
    {
        $scheduleData = json_decode($scheduleJson);

        $schedule = new Schedule();
        $events = collect();

        if($scheduleData == null){
            $schedule->setRelation('events', $events->unique());
            $schedule->name = 'new';

            return $schedule;
        }

        $schedule->name = $scheduleData->name;
        $ids = collect();

        foreach ($scheduleData->schedule as $day) {
            foreach ($day as $hour) {
                foreach ($hour as $event) {
                    if($events->where('name', $event->name)
                            ->where('from_hour', $event->from_hour)
                            ->where('to_hour', $event->to_hour)
                            ->where('day', $event->day)
                            ->where('start_date', $event->start_date)
                            ->first() !== null){
                        continue;
                    }

                    $data = collect($event);
                    $e = new \App\Models\Event($data->toArray());

                    if(isset($data['eid']))
                        $e->eid = $data['eid'];
                    else{
                        do{
                            $e->eid = rand(10000, 99999);
                        } while($ids->contains($e->eid));
                    }

                    $ids->push($e->eid);

                    $events->push($e);
                }
            }
        }

        $schedule->setRelation('events', $events->unique());

        return $schedule;
    }

    public function convertToJson($from_date = null, $to_date = null) : array | null
    {
        try {
            if(!$this->schedule)
                return null;

            $schedule = [];

            $schedule['name'] = $this->schedule->name;
            $schedule['primary_color'] = $this->schedule->primary_color;
            $schedule['secondary_color'] = $this->schedule->secondary_color;
            $schedule['background_color'] = $this->schedule->background_color;
            $schedule['schedule'] = [];
            $schedule['min_hour'] = 7;
            $schedule['max_hour'] = 15;

            if ($from_date && $to_date) {
                $period = CarbonPeriod::create($from_date, $to_date);
                if($period->count() > 7) throw new \Exception("More than 7 days");
                $days = [];

                foreach ($period as $date) {
                    $dayName = strtolower($date->format('D'));
                    $days[] = $dayName;
                }
            } else {
                $days = \App\Models\Event::AVALIABLE_DAYS;
            }

            foreach ($days as $day) {
                $query = collect($this->schedule->events)->filter(function ($event) use ($day, $from_date, $to_date) {
                    return $event->day === $day &&
                        ($event->start_date <= ($from_date || Carbon::now()) ) &&
                        (($event->end_date >= $to_date ?? Carbon::now()) || $event->end_date === null);
                })->sortBy('from_hour');

                if($query->count() <= 0)
                    $schedule['schedule'][$day] = [];

                $minHour = $query->min('from_hour');
                if($minHour !== null && $minHour < $schedule['min_hour']){
                    $schedule['min_hour'] = $minHour;
                }
                $maxHour = $query->max('to_hour');
                if($maxHour > $schedule['max_hour']){
                    $schedule['max_hour'] = $maxHour;
                }

                $hourly = [];

                while($minHour <= $maxHour){
                    $hourly[$minHour] = $query
                        ->filter(fn($event) =>
                            $event->from_hour <= $minHour && $event->to_hour >= $minHour
                        )
                        ->values()
                        ->toArray();

                    $minHour += 1;
                }

                $schedule['schedule'][$day] = $hourly;
            }

            return $schedule;
        } catch(\Exception $e){
            return null;
        }
    }

    public function generateIcal(){
        $calendar = Calendar::create($this->schedule->name);
        $events = [];

        foreach($this->schedule->events as $event){
            $e = json_decode(json_encode($event));

            $rrule = $event->end_date ?
                RRule::frequency(RecurrenceFrequency::Weekly)->until(new DateTime($e->end_date))
                :
                RRule::frequency(RecurrenceFrequency::Weekly);

            $events[] = Event::create($e->name)
                ->startsAt(new DateTime($e->start_date.' '.$e->from_hour.':00'))
                ->endsAt(new DateTime($e->start_date.' '.$e->to_hour.':00'))
                ->rrule($rrule);
        }

        return $calendar->event($events)->toString();
    }

    public function removeEvent($eventId) : void
    {
        if($this->schedule->id) {
            $this->events()->detach($eventId);
        } else {
            $this->schedule->events = $this->schedule->events->filter(function ($event) use ($eventId){
                return $event->eid != $eventId;
            });
        }
    }

    public function addEvents($events) : void
    {
        if($this->schedule->id) {
            $eventIds = [];

            foreach ($events as $eventData) {
                $event = \App\Models\Event::query()->findOrNew($eventData['id'] ?? null, $eventData);
                $eventIds[] = $event->id;
            }

            $this->schedule->events()->syncWithoutDetaching($eventIds);
        } else {
            $ids = collect($this->schedule->events)->pluck('eid');
            foreach ($events as $event) {
                $data = collect($event)->toArray();

                $e = new \App\Models\Event($data);
                do{
                    $e->eid = rand(10000, 99999);
                } while($ids->contains($e->eid));
                $ids->push($e->eid);
                $this->schedule->events->push($e);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function updateEvent($data) : void
    {
        if($this->schedule->id) {
            if(auth()->user()->id !== $this->schedule->user_id)
                throw new \Exception("Action prohibited");

            if($event = \App\Models\Event::find($data['id']))
                $event->update($data);
        } else {
            $this->removeEvent($data['eid']);
            $this->addEvents([$data]);
        }
    }

    /**
     * @throws \Exception
     */
    public function updateSchedule($data) : void
    {
        if($this->schedule->id) {
            $this->schedule->name = $data['name'];
            $this->schedule->primary_color = $data['primary_color'];
            $this->schedule->secondary_color = $data['secondary_color'];
            $this->schedule->background_color = $data['background_color'];
        } else {
            if(auth()->user()->id !== $this->schedule->user_id)
                throw new \Exception("Action prohibited");

            $this->schedule->update($data);
        }
    }
}
