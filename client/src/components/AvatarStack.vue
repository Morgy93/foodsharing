<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div
    class="pickup-entries"
    :style="{
      '--component-height': heightInPx + 'px',
      '--overlap': overlap + 'px',
      '--border-color': borderColor,
    }"
  >
    <!-- Flex container reverses elements for correct draw order without z-index -->
    <div
      v-if="freeSlots"
      class="free-slots"
    >
      <span>
        {{ $i18n(`pickup.overview.freeSlots`, {slots: freeSlots}) }}
      </span>
    </div>

    <div v-if="hiddenUsers.length" :id="'hidden-fetcher-'+uniqueId" class="hidden-users">
      <span>
        +{{ hiddenUsers.length }}
      </span>
    </div>
    <b-tooltip v-if="hiddenUsers.length && showOverflowTooltip" :target="'hidden-fetcher-'+uniqueId" triggers="hover">
      <span v-for="(user, index) in hiddenUsers" :key="user.id">
        <span v-if="index != 0">, </span>
        <a :href="$url('profile', user.id)" class="tooltip-link">{{ user.name }}</a>
      </span>
    </b-tooltip>

    <a
      v-for="(user, index) in shownUsers"
      :key="index"
      :href="$url('profile', user.id)"
    >
      <Avatar
        v-b-tooltip="user.name"
        :url="user.avatar"
        :size="heightInPx"
        round
        img-class="content"
        :class="index?'':'last-avatar'"
        :auto-scale="false"
      />
    </a>
  </div>
</template>

<script>
import Avatar from '@/components/Avatar'
import { v4 as uuidv4 } from 'uuid'

export default {
  components: { Avatar },
  props: {
    registeredUsers: {
      type: Array,
      default: () => [],
    },
    totalSlots: {
      type: Number,
      default: 0,
    },
    maxWidthInPx: {
      type: Number,
      default: 200,
    },
    heightInPx: {
      type: Number,
      default: 35,
    },
    overlap: {
      type: Number,
      default: 15,
    },
    borderColor: {
      type: String,
      default: 'white',
    },
    showOverflowTooltip: {
      type: Boolean,
      default: true,
    },
  },
  data () {
    return {
      uniqueId: uuidv4(),
    }
  },
  computed: {
    shownUsersNum () {
      // The free slots add-on needs 43px
      const width = this.maxWidthInPx - (this.freeSlots ? 43 : 0)
      const maxCircles = Math.max(1, Math.floor((width - this.overlap) / (this.heightInPx - this.overlap)))
      if (this.registeredUsers.length <= maxCircles) {
        return this.registeredUsers.length
      } else {
        return Math.max(maxCircles - 1, 1)
      }
    },
    shownUsers () {
      return this.registeredUsers.slice(0, this.shownUsersNum).reverse()
    },
    hiddenUsers () {
      return this.registeredUsers.slice(this.shownUsersNum)
    },
    freeSlots () {
      return Math.max(0, this.totalSlots - this.registeredUsers.length)
    },
  },
}
</script>
<style lang="scss" scoped>

.pickup-entries {
  display: inline-flex;
  flex-direction: row-reverse;

  & *:not(:last-child) {
    margin-left: calc(-1 * var(--overlap));
  }
}

::v-deep .avatar {
  img {
    // box-sizing: content-box;
    border: 1px solid var(--border-color);
    border-width: 1px 2px 1px 0;
  }
}

.hidden-users, .free-slots {
    align-items: center;
    border-radius: var(--component-height);
    border: 1px solid;
    display: flex;
    font-weight: bold;
    height: var(--component-height);
    padding: 0 6px 0 calc(var(--overlap) + 1px);
    min-width: var(--component-height);
    white-space: nowrap;

    &:last-child{
      padding-left: 6px;
    }

    span{
      width: 100%;
      text-align: center;
    }
  }

  .free-slots {
    background-color: var(--fs-color-secondary-500);
    border-color: var(--fs-color-secondary-600);
    color: var(--fs-color-light);
  }

  .hidden-users {
    border-color: var(--fs-color-primary-100);
    border-color: var(--fs-color-primary-300);
    color: var(--fs-color-primary-alpha-70);
    background-color: var(--fs-color-light);
  }
</style>
