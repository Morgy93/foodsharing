<template>
  <a
    class="d-flex flex-column"
    :class="classes"
    :href="$url('basket', basket.id)"
  >
    <div class="d-flex">
      <div
        class="icon w-20 mr-2 d-flex text-center justifiy-content-center align-items-center"
      >
        <img
          class="img-thumbnail"
          style="min-width: 42px;"
          src="/img/basket.png"
        >
      </div>
      <div class="d-flex flex-column justify-content-between truncated">
        <div class="d-flex justify-content-between align-items-center">
          <h5
            class="mb-1 text-truncate"
            v-html="basket.description"
          />
        </div>
        <small
          v-if="!basket.requests.length"
          class="mb-1 text-muted text-truncate"
          v-html="$i18n('basket.no_requests')"
        />

        <small
          v-if="basket.requests.length > 0"
          class="mb-1 text-truncate"
          v-html="$i18n('basket.requested_by', { name: basket.requests.map(r => r.user.name).join(', ') })"
        />
      </div>
    </div>
    <a
      v-for="(entry, key) in basket.requests"
      :key="key"
      href="#"
      class="requests img-thumbnail mt-1 d-flex align-items-center justify-content-between truncated"
      @click.prevent="openChat(entry.user.id, $event)"
    >
      <div class="d-flex">
        <avatar
          class="mx-2"
          :url="entry.user.avatar"
          :size="16"
          :sleep-status="entry.user.sleepStatus"
          :auto-scale="false"
        />
        <small
          v-b-tooltip="entry.user.name.length > 15 ? entry.user.name : null"
        >
          {{ truncate(entry.user.name, 15) }}
          {{ $dateDistanceInWords(entry.time) }}
        </small>
      </div>
      <button
        v-b-tooltip="$i18n('basket.request_close')"
        :title="$i18n('basket.request_close')"
        class="btn btn-sm btn-outline-danger"
        @click.prevent.stop="openRemoveDialog(entry.user.id, $event)"
      >
        <i class="fas fa-times" />
      </button>
    </a>
  </a>
</template>

<script>
import TruncateMixin from '@/mixins/TruncateMixin'

import Avatar from '@/components/Avatar'
import conv from '@/conv'

export default {
  components: { Avatar },
  mixins: [TruncateMixin],
  props: {
    basket: {
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
    classes () {
      return [
        'list-group-item',
        'list-group-item-action',
        this.basket.requests.length > 0 ? 'list-group-item-warning' : null,
      ]
    },
  },
  methods: {
    openChat (userId, e) {
      conv.userChat(userId)
    },
    openRemoveDialog (userId, e) {
      this.$emit('basket-remove', this.basket.id, userId)
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
