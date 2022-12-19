<template>
  <a
    class="dropdown-header dropdown-item d-flex justify-content-between align-items-center"
    :class="{
      'list-group-item-warning': !bell.isRead,
      'disabledLoading': bell.isDeleting,
    }"
    :href="bell.href"
    @click="$emit('read', bell)"
  >
    <div
      class="icon icon--big icon--rounded mr-2 d-flex text-center justifiy-content-center align-items-center"
    >
      <div
        class="icon icon-default"
      >
        <Avatar
          v-if="bell.image"
          class="icon icon--big icon--rounded img-thumbnail"
          :url="bell.image"
          :size="35"
        />
        <i
          v-else
          class="icon icon--big icon--rounded img-thumbnail align-items-center justify-content-center"
          :class="bell.icon"
        />
      </div>
      <i
        class="icon icon-close icon--big img-thumbnail align-items-center justify-content-center fas fa-times"
        @click.stop.prevent="closeBell()"
      />
    </div>
    <span class="d-flex w-100 flex-column text-truncate">
      <span class="d-flex justify-content-between align-items-center text-truncate">
        <span
          class="mb-1 text-truncate"
          v-html="$i18n(`bell.${bell.title}`, bell.payload)"
        />
        <small class="text-muted text-right nowrap">
          {{ $dateFormatter.relativeTime(new Date(bell.createdAt)) }}
        </small>
      </span>
      <small
        class="text-truncate"
        v-html="$i18n(`bell.${bell.key}`, bell.payload)"
      />
    </span>
  </a>
</template>

<script>
import Avatar from '@/components/Avatar'

import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Avatar,
  },
  mixins: [MediaQueryMixin],
  props: {
    bell: {
      type: Object,
      default: () => {},
    },
  },
  data () {
    return {
      hover: false,
    }
  },
  computed: {
    classes () {
      return [
        !this.bell.isRead ? 'list-group-item-warning' : null,
        this.bell.isDeleting ? 'disabledLoading' : null,
      ]
    },
  },
  methods: {
    closeBell () {
      if (this.bell.isCloseable) {
        this.$emit('remove', this.bell.id)
      }
    },
  },
}
</script>

<style lang="scss" scoped>
@import '../../../scss/icon-sizes.scss';

a:hover .icon-default,
a:not(:hover) .icon-close {
  display: none;
}

</style>
