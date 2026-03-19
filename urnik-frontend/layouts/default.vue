<template>
  <div>
    <modal class="z-30">
      <add-event-modal v-if="modalStore.modalType == 'addEvent'" />
      <edit-event-modal v-if="modalStore.modalType == 'editEvent'" />
      <login-modal v-if="modalStore.modalType == 'login'" />
      <register-modal v-if="modalStore.modalType == 'register'" />
      <edit-schedule-modal v-if="modalStore.modalType == 'editSchedule'" />
    </modal>

    <app-header />

    <slot />
  </div>
</template>

<script setup lang="ts">
import Modal from '../components/modal.vue'
import AddEventModal from "~/components/modals/addEventModal.vue";
import {useModalStore} from "~/stores/modal";
import {useFacultyStore} from "~/stores/faculty";
import LoginModal from "~/components/modals/loginModal.vue";
import RegisterModal from "~/components/modals/registerModal.vue";
import EditEventModal from "~/components/modals/editEventModal.vue";
import EditScheduleModal from "~/components/modals/editScheduleModal.vue";
import AppHeader from "~/components/appHeader.vue";

const modalStore = useModalStore()
const facultyStore = useFacultyStore()

onMounted(() => {
  facultyStore.fetchFaculties()
})
</script>

<style scoped>

</style>