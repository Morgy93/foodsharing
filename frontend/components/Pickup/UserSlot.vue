<template>
  <div v-if="slot">
    <p :class="{'me': slot.profile.id === userid}">
      <span v-text="slot.isConfirmed ? '✓': '×'" />
      <a :href="`/profile/${slot.profile.id}`">{{ slot.profile.name }}</a>
    </p>
  </div>
  <div v-else>
    <p>
      {{ $t('store.pickups.free') }}
    </p>
  </div>
</template>

<script setup>
const props = defineProps({
  slot: {
    type: Object
  },
})

const userid = useState('user').value.id

</script>

<style scoped>
.not-confirmed {
  opacity: 0.5;
}

.me {
  color: blue;
}
</style>
