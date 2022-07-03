<template>
  <b-modal
    id="searchBarModal"
    ref="searchBarModal"
    button-size="sm"
    size="lg"

    @shown="focusSearchbar"
  >
    <template #modal-header>
      <label
        class="sr-only"
        for="searchField"
        v-html="$i18n('search.placeholder')"
      />
      <i
        class="icon fas"
        :class="{
          'fa-search': !isLoading,
          'fa-spinner fa-spin': isLoading,
        }"
      />
      <input
        id="searchField"
        ref="searchField"
        v-model="query"
        type="text"
        class="form-control"
        :placeholder="$i18n('search.placeholder')"
      >
      <i
        class="icon icon-right fas"
        :class="{
          'fa-times is-clickable': query.length > 0,
        }"
        @click="query=''"
      />
    </template>
    <template
      #default
    >
      <search-results
        v-if="isOpen"
        class="results"
        :users="results.users || []"
        :regions="results.regions || []"
        :stores="results.stores || []"
        :food-share-points="results.foodSharePoints || []"
        :my-groups="index.myGroups"
        :my-regions="index.myRegions"
        :my-stores="index.myStores"
        :my-buddies="index.myBuddies"
        :query="query"
        :is-loading="isLoading"
      />
      <div
        v-else
        class="alert alert-info"
      >
        {{ $i18n('search.informations') }}
      </div>
    </template>
    <template #modal-footer="{ hide }">
      <b-button
        size="sm"
        variant="secondary"
        @click="hide('forget')"
      >
        {{ $i18n('globals.close') }}
      </b-button>
    </template>
  </b-modal>
</template>

<script>
// Components
import SearchResults from '@/components/SearchBar/SearchResults'
// Others
import { instantSearch, instantSearchIndex } from '@/api/search'
export default {
  components: { SearchResults },
  data () {
    return {
      query: '',
      isOpen: false,
      isLoading: false,
      isIndexLoaded: false,
      results: {
        stores: [],
        users: [],
        regions: [],
        foodSharePoints: [],
      },
      index: {
        myStores: [],
        myGroups: [],
        myRegions: [],
        myBuddies: [],
      },
    }
  },
  watch: {
    query (query) {
      if (!this.isIndexLoaded) {
        this.fetchIndex()
      }
      if (query.trim().length > 2) {
        this.isOpen = true
        this.delayedFetch()
      } else if (query.trim().length) {
        clearTimeout(this.timeout)
        this.isOpen = true
        this.isLoading = false
      } else {
        clearTimeout(this.timeout)
        this.isOpen = false
        this.isLoading = false
      }
    },
  },
  methods: {
    focusSearchbar () {
      this.$refs.searchField.focus()
    },
    delayedFetch () {
      if (this.timeout) {
        clearTimeout(this.timeout)
        this.timer = null
      }
      this.timeout = setTimeout(() => {
        this.fetch()
      }, 200)
    },
    close () {
      this.isOpen = false
    },
    async fetch () {
      const curQuery = this.query
      this.isLoading = true
      const res = await instantSearch(curQuery)
      if (curQuery !== this.query) {
        // query has changed, throw away this response
        return false
      }
      this.results = res
      this.isLoading = false
    },
    async fetchIndex () {
      this.isIndexLoaded = true
      this.index = await instantSearchIndex()
    },
    clickOutListener () {
      this.isOpen = false
    },
  },
}
</script>

<style lang="scss" scoped>
.is-clickable {
  cursor: pointer;
}

.icon {
  position: absolute;
  left: 1.25rem;
  font-size: 1.15rem;
  color: var(--fs-color-dark);
}

.icon-right {
  left: unset;
  right: 1.25rem;
}

::v-deep .modal-header {
  align-items: center;
  background-color: var(--fs-color-light);
  position: relative;
}

::v-deep.input-group-text {
  border: 0;
  background-color: var(--fs-color-transparent);

  i {
    min-width: 1rem;
  }
}

::v-deep.form-control {
  font-size: 1.5rem;
  border: 0;
  text-indent: 2rem;

  @media (max-width: 575.98px) {
    font-size: 1rem;
  }
}

::v-deep .alert {
  margin-bottom: 0;
}

::v-deep.results .dropdown-item,
::v-deep.results .dropdown-header {
  padding-left: 0;
}

</style>
