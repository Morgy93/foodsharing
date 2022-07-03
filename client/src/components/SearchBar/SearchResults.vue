<template>
  <div>
    <div
      v-if="isEmpty && !isLoading"
      class="alert alert-warning"
    >
      {{ $i18n('search.noresults') }}
    </div>
    <div
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
    </div>
  </div>
</template>

<script>
import SearchResultEntry from './SearchResultEntry'

function arrayFilterDuplicate (list, ignore) {
  const ids = ignore.map(e => e.id)
  return list.filter(e => ids.indexOf(e.id) === -1)
}

function match (word, e) {
  if (e.name && e.name.toLowerCase().indexOf(word) !== -1) return true
  if (e.teaser && e.teaser.toLowerCase().indexOf(word) !== -1) return true
  return false
}

export default {
  components: { SearchResultEntry },
  props: {
    stores: {
      type: Array,
      default: () => [],
    },
    groups: {
      type: Array,
      default: () => [],
    },
    regions: {
      type: Array,
      default: () => [],
    },
    foodSharePoints: {
      type: Array,
      default: () => [],
    },
    users: {
      type: Array,
      default: () => [],
    },
    myGroups: {
      type: Array,
      default: () => [],
    },
    myStores: {
      type: Array,
      default: () => [],
    },
    myRegions: {
      type: Array,
      default: () => [],
    },
    myBuddies: {
      type: Array,
      default: () => [],
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
    filtered () {
      const query = this.query.toLowerCase().trim()
      const words = query.match(/[^ ,;+.]+/g)

      // filter elements, whether all of the query words are contained somewhere in name or teaser
      const filterFunction = (e) => {
        if (!words.length) return false
        for (const word of words) {
          if (!match(word, e)) return false
        }
        return true
      }
      const res = {
        stores: this.stores.filter(filterFunction),
        regions: this.regions.filter(filterFunction),
        users: this.users.filter(filterFunction),
        groups: this.groups.filter(filterFunction),
        foodSharePoints: this.foodSharePoints.filter(filterFunction),
        myGroups: this.myGroups.filter(filterFunction),
        myStores: this.myStores.filter(filterFunction),
        myRegions: this.myRegions.filter(filterFunction),
        myBuddies: this.myBuddies.filter(filterFunction),
      }

      // additionally remove elements in global search which are already contained in the private lists

      res.stores = arrayFilterDuplicate(res.stores, res.myStores)
      res.groups = arrayFilterDuplicate(res.groups, res.myGroups)
      res.regions = arrayFilterDuplicate(res.regions, res.myRegions)
      res.users = arrayFilterDuplicate(res.users, res.myBuddies)

      // because myGroups are still contained in the regions response, we filter them out additionally
      res.regions = arrayFilterDuplicate(res.regions, res.myGroups)
      return res
    },
    isEmpty () {
      return (
        !this.filtered.stores.length &&
                !this.filtered.regions.length &&
                !this.filtered.users.length &&
                !this.filtered.groups.length &&
                !this.filtered.foodSharePoints.length &&
                !this.filtered.myGroups.length &&
                !this.filtered.myStores.length &&
                !this.filtered.myRegions.length &&
                !this.filtered.myBuddies.length
      )
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
