<template>
  <div id="search-bar">
    <b-input-group>
      <b-input-group-prepend>
        <b-input-group-text>
          <i
            class="fas"
            :class="{
              'fa-search': !isLoading,
              'fa-spinner fa-spin': isLoading,
            }"
          />
          <span
            class="hide-for-users"
            v-html="$i18n('search.placeholder')"
          />
        </b-input-group-text>
      </b-input-group-prepend>
      <b-form-input
        id="searchfield"
        v-model="query"
        autocomplete="off"
        :placeholder="$i18n('search.placeholder')"
      />
    </b-input-group>
    <div
      v-if="isOpen"
      id="search-results"
      class="dropdown-menu"
    >
      <search-results
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
    </div>
  </div>
</template>

<script>
import SearchResults from './SearchResults'
import { instantSearch, instantSearchIndex } from '@/api/search'
import clickOutMixin from 'bootstrap-vue/esm/mixins/click-out'
import listenOnRootMixin from 'bootstrap-vue/esm/mixins/listen-on-root'

export default {
  components: { SearchResults },
  mixins: [clickOutMixin, listenOnRootMixin],
  props: {
    showOnMobile: {
      type: Boolean,
      default: true,
    },
  },
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
    query (query, oldQuery) {
      if (!this.isIndexLoaded) {
        this.fetchIndex()
      }
      if (query.trim().length > 2) {
        this.open()
        this.delayedFetch()
      } else if (query.trim().length) {
        clearTimeout(this.timeout)
        this.open()
        this.isLoading = false
      } else {
        clearTimeout(this.timeout)
        this.close()
        this.isLoading = false
      }
    },
  },
  mounted () {
    // close the result box if another dropdown menu gets opened
    this.listenOnRoot('bv::dropdown::shown', this.close)
  },
  methods: {
    open () {
      this.isOpen = true
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
#search-bar {
  position: relative;
}

#search-bar,
#search-results {
  max-width: 320px;
  min-width: 320px;

  .collapse.show & {
    max-width: initial;
  }

  @media (min-width: 1200px) {
    max-width: 500px;
    min-width: 500px;
  }
}

#search-results {
  display: block;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0em 1em 5px -10px rgba(0, 0, 0, 0.35);
  left: 0;
  top: 30px;
}
</style>
