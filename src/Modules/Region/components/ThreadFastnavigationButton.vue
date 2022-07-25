<template>
  <button
    type="button"
    class="btn btn-primary btn-sm w-100 mb-2"
    @click="$emit('navigate')"
  >
    <i class="fas fa-arrow-right mr-1" /> {{ label }}
  </button>
</template>

<script>
export default {
  name: 'ThreadFastnavigationButton',
  props: {
    label: {
      type: String,
      default: '',
    },
  },
}
</script>

<style scoped>

</style>
