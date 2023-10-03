<template>
  <div>
    <div
      v-if="isEmpty && !isLoading"
      class="alert alert-warning"
    >
      {{ $i18n('search.noresults') }}
    </div>

    {{ results }}

    <!-- <div
      v-if="filtered.myBuddies.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-user" /> {{ $i18n('globals.type.my_buddies') }}
      </h3>
      <search-result-entry
        v-for="buddy in filtered.myBuddies"
        :key="buddy.id"
        :href="$url('profile', buddy.id)"
        :title="buddy.name"
        :teaser="buddy.teaser"
        :image="buddy.image"
      />
    </div>
    <div
      v-if="filtered.myGroups.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-users" /> {{ $i18n('globals.type.my_groups') }}
      </h3>
      <search-result-entry
        v-for="group in filtered.myGroups"
        :key="group.id"
        :href="$url('forum', group.id)"
        :title="group.name"
        :teaser="group.teaser"
        :image="group.image"
      />
    </div>
    <div
      v-if="filtered.myStores.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-shopping-cart" /> {{ $i18n('globals.type.my_stores') }}
      </h3>
      <search-result-entry
        v-for="store in filtered.myStores"
        :key="store.id"
        :href="$url('store', store.id)"
        :title="store.name"
        :teaser="store.teaser"
        :image="store.image"
      />
    </div>
    <div
      v-if="filtered.myRegions.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-home" /> {{ $i18n('globals.type.my_regions') }}
      </h3>
      <search-result-entry
        v-for="region in filtered.myRegions"
        :key="region.id"
        :href="$url('forum', region.id)"
        :title="region.name"
        :teaser="region.teaser"
        :image="region.image"
      />
    </div>

    <div
      v-if="filtered.groups.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-users" /> {{ $i18n('globals.type.groups') }}
      </h3>
      <search-result-entry
        v-for="group in filtered.groups"
        :key="group.id"
        :href="$url('forum', group.id)"
        :title="group.name"
        :teaser="group.teaser"
        :image="group.image"
      />
    </div>
    <div
      v-if="filtered.users.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-child" /> {{ $i18n('globals.type.persons') }}
      </h3>
      <search-result-entry
        v-for="user in filtered.users"
        :key="user.id"
        :href="$url('profile', user.id)"
        :title="user.name"
        :teaser="user.teaser"
        :image="user.image"
      />
    </div>
    <div
      v-if="filtered.stores.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-shopping-cart" /> {{ $i18n('globals.type.stores') }}
      </h3>
      <search-result-entry
        v-for="store in filtered.stores"
        :key="store.id"
        :href="$url('store', store.id)"
        :title="store.name"
        :teaser="store.teaser"
        :image="store.image"
      />
    </div>
    <div
      v-if="filtered.foodSharePoints.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-recycle" /> {{ $i18n('globals.type.foodshare_points') }}
      </h3>
      <search-result-entry
        v-for="foodSharePoint in filtered.foodSharePoints"
        :key="foodSharePoint.id"
        :href="$url('foodsharepoint', foodSharePoint.id)"
        :title="foodSharePoint.name"
        :teaser="foodSharePoint.teaser"
        :image="foodSharePoint.image"
      />
    </div>
    <div
      v-if="filtered.regions.length"
      class="entry"
    >
      <h3 class="dropdown-header">
        <i class="icon-subnav fas fa-home" /> {{ $i18n('globals.type.regions') }}
      </h3>
      <search-result-entry
        v-for="region in filtered.regions"
        :key="region.id"
        :href="$url('forum', region.id)"
        :title="region.name"
        :teaser="region.teaser"
        :image="region.image"
      />
    </div> -->
    <search-result-entry
      :href="$url('forum', 1)"
      title="Titel"
      teaser="Teaser"
      :image="null"
    />
  </div>
</template>

<script>
import SearchResultEntry from './SearchResultEntry'

export default {
  components: { SearchResultEntry },
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
</style>
