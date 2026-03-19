<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use MongoDB\BSON\Int64;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\RecurrenceFrequency;
use Spatie\IcalendarGenerator\ValueObjects\RRule;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
        'primary_color',
        'secondary_color',
        'background_color',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function events(){
        return $this->belongsToMany(\App\Models\Event::class, 'schedule_events', 'schedule_id', 'event_id');
    }
}
