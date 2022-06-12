<template>
  <a
    class="d-flex"
    :class="classes"
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
        style="min-width: 42px;"
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
    <div class="d-flex flex-column justify-content-between truncated">
      <div class="d-flex justify-content-between align-items-center">
        <h5
          class="mb-1 text-truncate"
        >
          {{ $i18n(`bell.${bell.title}`, bell.payload) }}
        </h5>
        <small class="text-muted text-right nowrap">
          {{ $dateDistanceInWords(bell.createdAt) }}
        </small>
      </div>
      <p
        class="mb-0 text-truncate"
      >
        {{ $i18n(`bell.${bell.key}`, bell.payload) }}
      </p>
    </div>
  </a>
</template>

<script>
import Avatar from '@/components/Avatar'
import StateTogglerMixin from '@/mixins/StateTogglerMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: {
    Avatar,
  },
  mixins: [StateTogglerMixin, MediaQueryMixin],
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
        'list-group-item',
        'list-group-item-action',
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
.list-group-item {
    padding: 0.4em 1em;
    border: unset;

    h5 {
      font-weight: bold;
      font-size: 0.9em;
    }

    p {
        font-size: 0.8em;
    }
}

.icon {
  height: 42px;
  width: 42px;
  line-height: 0.7em;
  font-size: 1.5rem;
}

.truncated {
  flex: 1;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
