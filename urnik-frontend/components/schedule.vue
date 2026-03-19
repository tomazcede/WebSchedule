<template>
<div class="w-full">
  <div class="relative">
    <div class="w-full mb-4 justify-center flex flex-row gap-4">

      <VueDatePicker
          class="w-50 md:w-25"
          v-model="dateRange"
          :range="{ maxRange: 6 }"
          @update:model-value="datesChanged"
          :enable-time-picker="false"
          :clearable="false"
      />
    </div>

    <button class="absolute right-10 md:right-40 top-4" :title="$t('add_new_event')" @click="sendData">
      <img :src="'/img/add.png'" alt="Edit" width="20" height="20" />
    </button>
  </div>
  <div class="hoverdiv relative group w-full md:w-[75%] mx-auto">
    <button
        class="hoverbtn absolute top-2 right-2 rounded shadow z-10"
        @click="openEditScheduleModal"
    >
      <img :src="'/img/edit.png'" alt="Edit" width="20" height="20" />
    </button>

    <ScheduleTable />
  </div>
</div>
</template>

<script setup lang="ts">
import { useScheduleStore } from "~/stores/schedule";
import {computed} from "vue";
import {useModalStore} from "~/stores/modal";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import {useUserStore} from "~/stores/user";
import {useEventStore} from "~/stores/event";
import ScheduleTable from "~/components/schedule/scheduleTable.vue";

const modalStore = useModalStore()
const userStore = useUserStore()
const scheduleStore = useScheduleStore()

function datesChanged(){
  const id = userStore.user?.default_schedule?.id ?? null

  scheduleStore.getSchedule(id, dateRange.value[0], dateRange.value[1]);
}

async function sendData(){
  modalStore.isVisible = true
  modalStore.modalType = 'addEvent'
}

const dateRange = ref();

function formatDate(date) {
  return date.toISOString().split('T')[0]
}
function openEditScheduleModal() {
  modalStore.isVisible = true
  modalStore.modalType = 'editSchedule'
}

onMounted(async () => {
  const today = new Date()

  const day = today.getDay()
  const diffToMonday = today.getDate() - day + (day === 0 ? -6 : 1)
  const diffToSunday = today.getDate() - day + (day === 0 ? 0 : 7)

  const monday = new Date(today)
  monday.setDate(diffToMonday)
  const sunday = new Date(today)
  sunday.setDate(diffToSunday)
  dateRange.value = [formatDate(monday), formatDate(sunday)];

  await userStore.getCurrentUser()
  const id = userStore.user?.default_schedule?.id ?? null

  await scheduleStore.getSchedule(id, dateRange.value[0], dateRange.value[1]);
})

</script>

<style scoped>
.hoverbtn {
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
}

.hoverdiv:hover{
  .hoverbtn {
    opacity: 1;
  }
}

</style>