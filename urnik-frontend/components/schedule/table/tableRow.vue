<script setup lang="ts">
import {useScheduleStore} from "~/stores/schedule";
import Timeslot from "~/components/schedule/table/row/timeslot.vue";

const { hour } = defineProps(['hour'])

const isMobile = useIsMobile()

const scheduleStore = useScheduleStore()

const schedule = computed(() => scheduleStore.schedule)
const colors = computed(() => scheduleStore.colors)
const days = computed(() => Object.keys(scheduleStore.schedule))

function formatHour(hour: number) {
  return (hour.toString().length == 1 ? '0' + hour : hour) + ':00'
}
</script>

<template>
  <tr
      class="hover:bg-gray-50 transition-colors"
      style="height: 100%"
      :id="'hour-' + hour"
  >
    <td v-if="!isMobile"
        class="border border-gray-300 text-center font-medium text-sm"
        :style="colors.secondary_color ? 'background-color: ' + colors.secondary_color + ';' : 'background-color: var(--color-gray-50);'">
      {{ formatHour(hour) }}
    </td>
    <td
        v-for="day in days"
        :key="day"
        class="border border-gray-300 align-top relative"
        style="overflow: clip; height: inherit"
        :style="{
              overflow: 'clip',
              height: (schedule[day][hour] && schedule[day][hour].length) ? 'inherit' : 'calc(var(--spacing) * 16)',
              backgroundColor: colors.background_color || ''
            }"
    >
      <Timeslot :day="day" :hour="hour" />
      <span v-if="isMobile && day == days[0]" class="absolute bottom-0">{{ formatHour(hour) }}</span>
    </td>
  </tr>
</template>

<style scoped>

</style>