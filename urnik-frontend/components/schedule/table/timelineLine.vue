<script setup lang="ts">
const timeLineStyle = ref('display: none');

onMounted(() => {
  setInterval(() => {
    const nowDate = new Date();
    const hour = nowDate.getHours();
    const min = nowDate.getMinutes();

    const timeslot = document.getElementById('hour-' + hour)

    if(timeslot){
      let height = (min / 60) * timeslot.offsetHeight

      for(let i = 0; i < hour; i++){
        let slot = document.getElementById('hour-' + i)

        if(slot)
          height += slot.offsetHeight
      }

      timeLineStyle.value = `top: ${height}px; height: 1px;`
    } else {
      timeLineStyle.value = 'display: none'
    }
  }, 1000);
})
</script>

<template>
  <div
      class="absolute w-full bg-red-500"
      :style="timeLineStyle"
  ></div>
</template>

<style scoped>

</style>