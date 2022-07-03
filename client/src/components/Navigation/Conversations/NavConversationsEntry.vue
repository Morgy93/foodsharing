<template>
  <button
    class="dropdown-header dropdown-item d-flex justify-content-between align-items-center"
    :class="{
      'list-group-item-warning': conversation.hasUnreadMessages,
    }"
    @click="openChat"
  >
    <div
      class="mr-2"
    >
      <div
        class="icon icon--big avatars img-thumbnail"
        :class="[`avatars_${avatars.length}`]"
      >
        <Avatar
          v-for="(avatar) in avatars"
          :key="avatar"
          :url="avatar"
          :size="35"
        />
      </div>
    </div>
    <span class="d-flex w-100 flex-column text-truncate">
      <span class="d-flex justify-content-between align-items-center text-truncate">
        <span
          class="mb-1 text-truncate"
          v-html="title"
        />
        <small class="text-muted text-right nowrap">
          {{ relativeTime(conversation.lastMessage.sentAt) }}
        </small>
      </span>
      <small
        class="text-truncate"
        v-html="conversation.lastMessage.body"
      />
    </span>
  </button>
</template>
<script>
import DataUser from '@/stores/user'
import conv from '@/conv'
import profileStore from '@/stores/profiles'

import Avatar from '@/components/Avatar'
import DateFormatterMixin from '@/mixins/DateFormatterMixin'

export default {
  components: {
    Avatar,
  },
  mixins: [
    DateFormatterMixin,
  ],
  props: {
    conversation: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    title () {
      if (this.conversation.title) return this.conversation.title
      return this.filteredMemberList()
        .map(m => profileStore.profiles[m].name)
        .join(', ')
    },
    avatars () {
      const lastId = this.conversation.lastMessage.authorId
      return this.filteredMemberList()
        // bring last participant to the top
        .sort((a, b) => {
          /* eslint-disable eqeqeq */
          if (a == lastId) return -1
          if (b == lastId) return 1
          return 0
        })
        // we dont need more then 4
        .slice(0, 4)
        .map(m => profileStore.profiles[m].avatar)
    },
    loggedinUser () {
      return DataUser.getters.getUser()
    },
  },
  methods: {
    openChat () {
      conv.chat(this.conversation.id)
    },
    filteredMemberList () {
      return this.conversation.members
        // without ourselve
        .filter(m => m !== this.loggedinUser.id)
    },
  },
}
</script>

<style lang="scss" scoped>
@import '../../../scss/icon-sizes.scss';

::v-deep.avatars {
  &.avatars_2,
  &.avatars_3,
  &.avatars_4 {
    display: grid;
    & .avatar {
      display: inline-flex;
      border-radius: 0;
      overflow: hidden;

      & img {
        border-radius: 0 !important;
      }
    }
  }
}

::v-deep.avatars_2 {
  .avatar {
    &:nth-child(1) {
      grid-area: 1 / 1;
      border-top-left-radius: var(--border-radius);
      border-bottom-left-radius: var(--border-radius);
    }

    &:nth-child(2) {
      grid-area: 1 / 2;
      border-top-right-radius: var(--border-radius);
      border-bottom-right-radius: var(--border-radius);
    }

    & img {
      transform: translateX(-25%);
    }
  }
}

::v-deep.avatars_3 .avatar {
  &:nth-child(1) {
    grid-area: 1 / 1;
    border-top-left-radius: var(--border-radius);
  }

   &:nth-child(2) {
    grid-area: 1 / 2;
    border-top-right-radius: var(--border-radius);
    border-bottom-right-radius: var(--border-radius);
  }

  &:nth-child(3) {
    grid-area: 2 / 1;
    border-bottom-left-radius: var(--border-radius);
    border-bottom-right-radius: var(--border-radius);
  }
}

::v-deep.avatars_4 .avatar {
  &:nth-child(1) {
    grid-area: 1 / 1;
    border-top-left-radius: var(--border-radius);
  }

   &:nth-child(2) {
    grid-area: 1 / 2;
    border-top-right-radius: var(--border-radius);
  }

  &:nth-child(3) {
    grid-area: 2 / 1;
    border-bottom-left-radius: var(--border-radius);
  }

    &:nth-child(4) {
    grid-area: 2 / 2;
    border-bottom-right-radius: var(--border-radius);
  }
}

.nowrap {
    white-space: nowrap;
}
</style>
