<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <div :style="{minWidth: minWidth+'px', maxWidth: width+'px'}">
    <div class="pickup-entries">
      <!-- Flex container reverses elements for correct draw order without z-index -->
      <div v-if="freeSlots" class="free-slots">
        <span>
          {{ $i18n(`pickup.overview.freeSlots`, {slots: freeSlots}) }}
        </span>
      </div>

      <div v-if="hiddenUsersNum" :id="'hidden-fetchers-'+uniqueId" class="hidden-users">
        <span>
          +{{ hiddenUsersNum }}
        </span>
      </div>
      <b-tooltip v-if="hiddenUsersNum" :target="'hidden-fetchers-'+uniqueId" triggers="hover">
        <span v-for="(user, index) in hiddenUsers" :key="user.id">
          <span v-if="index != 0">, </span>
          <a :href="'/profile/' + user.id" class="tooltip-link">{{ user.name }}</a>
        </span>
      </b-tooltip>

      <a
        v-for="(user, index) in shownUsers"
        :key="user.url+' '+index"
        :href="`/profile/${user.id}`"
      >
        <Avatar
          v-b-tooltip.hover="user.name"
          :url="user.avatar"
          round
          img-class="content"
          :class="index?'':'last-avatar'"
          :auto-scale="false"
        />
      </a>
    </div>
  </div>
</template>

<script>
import Avatar from '@/components/Avatar'
import { v4 as uuidv4 } from 'uuid'

export default {
  components: { Avatar },
  props: {
    minWidth: {
      type: Number,
      default: 100,
    },
    maxWidth: {
      type: Number,
      default: 200,
    },
    registeredUsers: {
      type: Array,
      default: () => [],
    },
    totalSlots: {
      type: Number,
      default: 0,
    },
  },
  data () {
    return {
      uniqueId: uuidv4(),
    }
  },
  computed: {
    shownUsersNum () {
      // Circles are 37px wide and 20px apart, so n circles need 17+20n px
      // The free slots add-on needs 38px
      const width = this.width - (this.freeSlots ? 38 : 0)
      const maxCircles = Math.floor((width - 17) / 20)
      if (this.registeredUsers.length <= maxCircles) {
        return this.registeredUsers.length
      } else {
        return maxCircles - 1
      }
    },
    hiddenUsersNum () {
      return Math.max(0, this.registeredUsers.length - this.shownUsersNum)
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
    width () {
      return Math.max(this.minWidth, this.maxWidth)
    },
  },
}
</script>

<style lang="scss">

.pickup-entries {
  display: inline-flex;
  flex-direction: row-reverse;
  width: max-content;
  .avatar {
    width: 20px;
    img {
      border: 2px solid white;
      border-width: 1px 2px 1px 0;
    }
  }
  a:first-child .avatar {
    width: auto;
  }
  .hidden-users, .free-slots {
    height: 37px;
    line-height: 33px;
    border-radius: 37px;
    text-align: center;
    font-weight: bold;
    padding-left: 12px;
    border: 2px solid;

    span {
      font-size: 12px;
    }
  }
  .free-slots {
    width: 58px;
    border-color: #4e871c;
    background-color: var(--fs-green);
    color: white;

    &:last-child {
      padding-left: 0;
    }
  }
  .hidden-users {
    width: 37px;
    border-color: var(--fs-beige);
    background-color: #fcfaee;
    color: rgba(var(--fs-brown-rgb), 0.75);
  }
  .free-slots + .hidden-users {
    margin-right: -15px;
  }
}

</style>
