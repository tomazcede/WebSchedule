<script setup lang="ts">
import TableHead from "~/components/schedule/table/tableHead.vue";
import TimelineLine from "~/components/schedule/table/timelineLine.vue";
import {useScheduleStore} from "~/stores/schedule";
import TableRow from "~/components/schedule/table/tableRow.vue";

const scheduleStore = useScheduleStore()

const hours = computed(() => {
  const min = scheduleStore.minHour
  const max = scheduleStore.maxHour
  return Array.from({ length: max - min + 1 }, (_, i) => i + min)
})
</script>

<template>
  <table class="w-full table-fixed border-collapse border border-gray-300 text-xs" style="height: 1px">
    <TableHead />
    <tbody class="relative">
    <TimelineLine />
    <TableRow v-for="hour in hours" :key="hour" :hour="hour" />
    </tbody>
  </table>
</template>

<style scoped>

</style>