<template>
  <a
    :class="bellClasses"
    :href="bell.href"
    @click="$emit('bell-read', bell)"
    @click.middle="$emit('bell-read', bell)"
  >
    <div class="d-flex">
      <div class="icon w-20 mr-1 d-flex justifiy-content-center align-items-center">
        <i
          v-if="bell.icon"
          :class="[bell.icon, {'fas fa-times': hover}]"
          @mouseover="hover = true"
          @mouseout="hover = false"
          @click.stop.prevent="bell.isCloseable && $emit('remove', bell.id)"
        />
        <div v-if="bell.image">
          <img :src="bell.image">
        </div>
      </div>
      <div class="w-100">
        <div class="mt-1 d-flex justify-content-between">
          <h5
            class="mb-1 text-truncate"
            style="max-width: 150px"
          >
            {{ $i18n(`bell.${bell.title}`, bell.payload) }}
          </h5>
          <small class="text-muted text-right nowrap">
            {{ $dateDistanceInWords(bell.createdAt) }}
          </small>
        </div>
        <p
          class="mb-1 text-truncate"
          style="max-width: 200px"
        >
          {{ $i18n(`bell.${bell.key}`, bell.payload) }}
        </p>
      </div>
    </div>
  </a>
</template>

<script>
export default {
  props: {
    bell: {
      type: Object,
      default: () => ({}),
    },
  },
  data () {
    return {
      hover: false,
    }
  },
  computed: {
    bellClasses () {
      return [
        'list-group-item',
        'list-group-item-action',
        'flex-row',
        !this.bell.isRead ? 'list-group-item-warning' : null,
        this.bell.isDeleting ? 'disabledLoading' : null,
      ]
    },
  },
}
</script>

<style lang="scss" scoped>
h5 {
    font-weight: bold;
    font-size: 0.9em;
}
p {
    font-size: 0.8em;
}

.list-group-item {
  border: unset;
  border-radius: 0 !important;
}

.icon {
  width: 3rem;
  height: 3rem;
  font-size: 1.5rem;
}
</style>
