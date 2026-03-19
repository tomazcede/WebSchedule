<script setup lang="ts">
import {useScheduleStore} from "~/stores/schedule";

const { day, hour } = defineProps(['day', 'hour'])
const scheduleStore = useScheduleStore()
const eventStore = useEventStore()

const schedule = computed(() => scheduleStore.schedule)
const isMobile = useIsMobile()

function formatMobile(text: string) {
  if(!isMobile)
    return text

  return text.length <= 3 ? text : text.substring(0, 3) + "."
}
</script>

<template>
  <div
      class="row gap-1"
      v-if="schedule[day][hour] && schedule[day][hour].length"
      style="height: 100%"
  >
    <div
        v-for="event in schedule[day][hour]"
        class="bg-blue-100 text-blue-800 text-xs py-1 rounded shadow-sm col flex-1 px-4"
        :style="event.color ? 'background-color: ' + event.color + '; color: black;' : ''"
        :key="event.id"
        @click = "eventStore.openEditModal(event)"
    >
      {{ formatMobile(event.name) }}
    </div>
  </div>
  <div v-else class="h-full">

  </div>
</template>

<style scoped>

</style>