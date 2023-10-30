<template>
  <div>
    <div
      v-if="isEmpty && !isLoading"
      class="alert alert-warning"
    >
      {{ $i18n('search.noresults') }}
    </div>

    <div
      v-if="results.regions.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-globe" /> {{ $i18n('globals.type.regions') }}
      </h3>
      <RegionResultEntry
        v-for="region in results.regions"
        :key="region.id"
        :region="region"
      />
    </div>

    <div
      v-if="results.workingGroups.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-users" /> {{ $i18n('globals.type.groups') }}
      </h3>
      <WorkingGroupResultEntry
        v-for="group in results.workingGroups"
        :key="group.id"
        :working-group="group"
      />
    </div>

    <div
      v-if="results.users.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-user" /> {{ $i18n('globals.type.persons') }}
      </h3>
      <UserResultEntry
        v-for="user in results.users"
        :key="user.id"
        :user="user"
        @close="$emit('close')"
      />
    </div>

    <div
      v-if="results.stores.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-shopping-cart" /> {{ $i18n('globals.type.stores') }}
      </h3>
      <StoreResultEntry
        v-for="store in results.stores"
        :key="store.id"
        :store="store"
      />
    </div>

    <div
      v-if="results.threads.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-comments" /> {{ $i18n('globals.type.threads') }}
      </h3>
      <ThreadResultEntry
        v-for="thread in results.threads"
        :key="thread.id"
        :thread="thread"
      />
    </div>

    <div
      v-if="results.chats.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-comment" /> {{ $i18n('globals.type.chats') }}
      </h3>
      <ChatResultEntry
        v-for="chat in results.chats"
        :key="chat.id"
        :chat="chat"
        @close-modal="$emit('close')"
      />
    </div>

    <div
      v-if="results.foodSharePoints.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-recycle" /> {{ $i18n('globals.type.foodshare_points') }}
      </h3>
      <FoodSharePointResultEntry
        v-for="foodSharePoint in results.foodSharePoints"
        :key="foodSharePoint.id"
        :food-share-point="foodSharePoint"
      />
    </div>
  </div>
</template>

<script>
import UserResultEntry from './ResultEntry/UserResultEntry'
import WorkingGroupResultEntry from './ResultEntry/WorkingGroupResultEntry'
import RegionResultEntry from './ResultEntry/RegionResultEntry'
import StoreResultEntry from './ResultEntry/StoreResultEntry'
import FoodSharePointResultEntry from './ResultEntry/FoodSharePointResultEntry'
import ChatResultEntry from './ResultEntry/ChatResultEntry'
import ThreadResultEntry from './ResultEntry/ThreadResultEntry'

export default {
  components: { UserResultEntry, WorkingGroupResultEntry, RegionResultEntry, StoreResultEntry, FoodSharePointResultEntry, ChatResultEntry, ThreadResultEntry },
  props: {
    results: {
      type: Object,
      default: () => ({
        regions: [],
        workingGroups: [],
        stores: [],
        foodSharePoints: [],
        chats: [],
        threads: [],
        users: [],
      }),
    },
    query: {
      type: String,
      default: '',
    },
    isLoading: {
      type: Boolean,
      default: true,
    },
  },
  computed: {
    isEmpty () {
      return Object.values(this.results).every(value => value.length === 0)
    },
  },
}
</script>

<style lang="scss" scoped>
@import '../../scss/icon-sizes.scss';

.entry:not(:last-child) {
  padding-bottom: 1rem;
  margin-bottom: 1rem;
  border-bottom: 1px solid var(--fs-border-default);
}

.entry ::v-deep .btn {
  height: fit-content;
  padding-top: 4px;
  padding-bottom: 4px;
}

</style>
