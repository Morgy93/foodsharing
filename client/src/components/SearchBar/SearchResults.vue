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

    {{ results.regions }}

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

    <!--
    
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
    -->
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
import UserResultEntry from './ResultEntry/UserResultEntry'
import WorkingGroupResultEntry from './ResultEntry/WorkingGroupResultEntry'
import RegionResultEntry from './ResultEntry/RegionResultEntry'

export default {
  components: { SearchResultEntry, UserResultEntry, WorkingGroupResultEntry, RegionResultEntry },
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
