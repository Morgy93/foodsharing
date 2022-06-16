<template>
  <a
    class="dropdown-header dropdown-item d-flex justify-content-between align-items-center"
    :class="{
      'list-group-item-warning': !bell.isRead,
      'disabledLoading': bell.isDeleting,
    }"
    :href="bell.href"
    @click="$emit('read', bell)"
    @mouseover="viewIsMD && toggleState()"
    @mouseout="viewIsMD && toggleState()"
  >
    <div
      class="icon w-20 mr-2 d-flex text-center justifiy-content-center align-items-center"
      @click.stop.prevent="toggleState()"
    >
      <i
        v-if="bell.icon && !state"
        class="d-flex img-thumbnail w-100 h-100 align-items-center justify-content-center"
        :class="[bell.icon]"
      />
      <Avatar
        v-else-if="bell.image && !state"
        class="img-thumbnail"
        :url="bell.image"
        :size="35"
      />
      <i
        v-else
        class="d-flex img-thumbnail w-100 h-100 align-items-center justify-content-center"
        :class="'fas fa-times'"
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
          {{ relativeTime(bell.createdAt) }}
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

import StateTogglerMixin from '@/mixins/StateTogglerMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import DateFormatterMixin from '@/mixins/DateFormatterMixin'

export default {
  components: {
    Avatar,
  },
  mixins: [StateTogglerMixin, MediaQueryMixin, DateFormatterMixin],
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
.icon {
  height: 35px;
  width: 35px;
  line-height: 0.7em;
  font-size: 1.5rem;
}
</style>
