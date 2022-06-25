<template>
  <a
    class="dropdown-header dropdown-item"
    :class="{
      'list-group-item-warning': basket.requests.length > 0,
    }"
    :href="$url('basket', basket.id)"
  >
    <span
      class="d-flex justify-content-between align-items-center text-truncate"
    >
      <div
        class="mr-2"
      >
        <div
          class="img-thumbnail"
        >
          <img
            width="35px"
            style="max-widht: 35px;"
            src="/img/basket.png"
            loading="lazy"
          >
        </div>
      </div>
      <span class="d-flex flex-column text-truncate">
        <span class="d-flex justify-content-between align-items-center text-truncate">
          <span
            class="mb-1 text-truncate"
            v-html="basket.description"
          />
          <small class="text-muted text-right nowrap">
            {{ relativeTime(basket.createdAt) }}
          </small>
        </span>
        <small
          v-if="!basket.requests.length"
          class="mb-1 text-truncate"
          v-html="$i18n('basket.no_requests')"
        />
        <small
          v-if="basket.requests.length > 0"
          class="mb-1 text-truncate"
          v-html="$i18n('basket.requested_by', { name: basket.requests.map(r => r.user.name).join(', ') })"
        />
      </span>
    </span>
    <button
      v-for="(entry, key) in basket.requests"
      :key="key"
      class="requests  w-100 img-thumbnail mt-1 d-flex align-items-center justify-content-between truncated"
      @click.prevent="openChat(entry.user.id, $event)"
    >
      <div class="d-flex">
        <avatar
          class="mx-2"
          :url="entry.user.avatar"
          :size="16"
          :is-sleeping="entry.user.sleepStatus"
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
    </button>
  </a>
</template>

<script>
import TruncateMixin from '@/mixins/TruncateMixin'
import DateFormatterMixin from '@/mixins/DateFormatterMixin'

import Avatar from '@/components/Avatar'
import conv from '@/conv'

export default {
  components: { Avatar },
  mixins: [TruncateMixin, DateFormatterMixin],
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

  methods: {
    openChat (userId) {
      conv.userChat(userId)
    },
    openRemoveDialog (userId) {
      this.$emit('basket-remove', this.basket.id, userId)
    },
  },
}
</script>
