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
      <div class="icon icon--rounded mr-2 d-flex text-center justifiy-content-center align-items-center">
        <img
          v-if="basket.picture"
          class="icon icon--big icon--rounded img-thumbnail d-flex align-items-center justify-content-center"
          :src="getImageUrl(basket.picture)"
        >
        <i
          v-else
          class="fas fa-shopping-basket icon icon--big d-flex img-thumbnail align-items-center justify-content-center"
        />
      </div>
      <span class="w-100 d-flex flex-column text-truncate">
        <span class="d-flex justify-content-between align-items-center text-truncate">
          <span
            class="mb-1 text-truncate"
            v-html="basket.description"
          />
          <small class="time-ago text-right nowrap">
            {{ $dateFormatter.relativeTime(basket.createdAt) }}
          </small>
        </span>
        <small
          v-if="!basket.requests.length"
          class="mb-1 text-truncate"
          v-text="$i18n('basket.no_requests')"
        />
        <small
          v-if="basket.requests.length > 0"
          class="testing-basket-requested-by mb-1 text-truncate"
          v-text="$i18n('basket.requested_by', { name: basket.requests.map(r => r.user.name).join(', ') })"
        />
      </span>
    </span>
    <button
      v-for="(entry, key) in basket.requests"
      :key="key"
      class="testing-basket-requests w-100 img-thumbnail mt-1 d-flex align-items-center justify-content-between truncated"
      @click.prevent="openChat(entry.user.id, $event)"
    >
      <div class="d-flex align-items-center">
        <Avatar
          class="mr-2"
          :url="entry.user.avatar"
          :size="24"
          :is-sleeping="entry.user.sleepStatus"
          :auto-scale="false"
        />
        <small>
          {{ entry.user.name }}
          {{ $dateFormatter.relativeTime(entry.time) }}
        </small>
      </div>
      <button
        v-b-tooltip.left="$i18n('basket.request_close')"
        :title="$i18n('basket.request_close')"
        class="testing-basket-requests-close btn btn-sm btn-outline-danger"
        @click.prevent.stop="openRemoveDialog(entry.user.id, $event)"
      >
        <i class="fas fa-times" />
      </button>
    </button>
  </a>
</template>

<script>
// Others
import Avatar from '@/components/Avatar'
import conversationStore from '@/stores/conversations'

export default {
  components: { Avatar },
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
    getImageUrl (picture) {
      if (picture) {
        return `/images/basket/thumb-${picture}`
      } else {
        return '/img/basket.png'
      }
    },
    openChat (userId) {
      conversationStore.openChatWithUser(userId)
    },
    openRemoveDialog (userId) {
      this.$emit('basket-remove', this.basket.id, userId)
    },
  },
}
</script>
<style lang="scss" scoped>
.time-ago {
  color: var(--fs-color-grey-alpha-40);
  margin-left: 1rem;
}
</style>
