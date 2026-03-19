<template>
  <div v-if="isVisible" class="fixed top-0 left-0 w-full h-screen z-50 flex items-center justify-center">
    <slot />
  </div>

  <div
      class="fixed top-0 left-0 w-full h-screen bg-black opacity-50 z-20"
      v-if="modalStore.isVisible"
  >

  </div>
</template>

<script setup lang="ts">
import {useModalStore} from "~/stores/modal";

const modalStore = useModalStore()

const isVisible = computed(() => modalStore.isVisible)
</script>