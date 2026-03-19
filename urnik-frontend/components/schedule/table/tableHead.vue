<script setup lang="ts">
import {computed} from "vue";
import {useScheduleStore} from "~/stores/schedule";

const scheduleStore = useScheduleStore()
const isMobile = useIsMobile()
const colors = computed(() => scheduleStore.colors)
const days = computed(() => Object.keys(scheduleStore.schedule))

function formatMobile(text: string) {
  if(!isMobile)
    return text

  return text.length <= 3 ? text : text.substring(0, 3) + "."
}
</script>

<template>
  <thead>
  <tr :style="colors.primary_color ? 'background-color: ' + colors.primary_color + ';' : 'background-color: var(--color-gray-100);'">
    <th v-if="!isMobile" class="border border-gray-300 p-2 w-20"></th>
    <th
        v-for="day in days"
        :key="day"
        class="border border-gray-300 p-2 text-center font-semibold text-gray-700"
    >
      {{ formatMobile($t(day)) }}
    </th>
  </tr>
  </thead>
</template>

<style scoped>

</style>