<template>
  <div
    class="icon icon--big avatars img-thumbnail"
    :class="[`avatars_${avatars.length}`]"
  >
    <Avatar
      v-for="(avatar, index) in avatars"
      :key="'avatar_' + index"
      :url="avatar"
      :size="35"
    />
  </div>
</template>

<script>

import DataUser from '@/stores/user'
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
  data () {
    return {
      defaultAvatar: '/img/mini_q_avatar.png',
    }
  },
  computed: {
    loggedinUser () {
      return DataUser.getters.getUser()
    },
    avatars () {
      const lastId = this.conversation?.lastMessage?.authorId

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
        .map((m) => {
          const userProfile = profileStore.profiles[m]
          return userProfile?.avatar || this.defaultAvatar
        })
    },
  },
  methods: {
    filteredMemberList () {
      return this.conversation.members
        // without ourselve
        .filter(m => m !== this.loggedinUser.id)
    },
  },
}
</script>

<style lang="scss" scoped>

@import '../scss/icon-sizes.scss';

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

</style>
