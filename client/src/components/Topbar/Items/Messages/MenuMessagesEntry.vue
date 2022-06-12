<template>
  <a
    class="d-flex"
    :class="classes"
    href="#"
    @click="openChat"
  >
    <div
      class="icon w-20 mr-2 d-flex text-center justifiy-content-center align-items-center"
    >
      <div
        class="avatars img-thumbnail"
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
    <div class="d-flex flex-column justify-content-between truncated">
      <div class="d-flex justify-content-between align-items-center">
        <h5
          class="mb-1 text-truncate"
        >
          {{ title }}
        </h5>
        <small class="text-muted text-right nowrap">
          {{ $dateDistanceInWords(conversation.lastMessage.sentAt) }}
        </small>
      </div>
      <p class="mb-0 text-truncate">
        {{ conversation.lastMessage.body }}
      </p>
    </div>
  </a>
</template>
<script>
import serverData from '@/server-data'
import conv from '@/conv'
import profileStore from '@/stores/profiles'

import Avatar from '@/components/Avatar'

export default {
  components: {
    Avatar,
  },
  props: {
    conversation: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    classes () {
      return [
        'list-group-item',
        'list-group-item-action',
        'align-items-start',
        this.conversation.hasUnreadMessages ? 'list-group-item-warning' : null,
      ]
    },
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
      return serverData.user
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

::v-deep.avatars {
  &.avatars_2,
  &.avatars_3,
  &.avatars_4 {
    display: grid;
    width: 42px;
    height: 42px;
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
      // border-top-left-radius: var(--border-radius);
      border-top-right-radius: var(--border-radius);
      border-bottom-right-radius: var(--border-radius);
      // border-bottom-left-radius: var(--border-radius);
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

.truncated {
  flex: 1;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
