<template>
  <div
    id="searchBarModal"
    tabindex="-1"
    class="modal fade"
    aria-labelledby="searchBarModal"
    aria-hidden="true"
  >
    <div class="modal-dialog">
      <div class="modal-content">
        <div
          class="modal-header"
          :class="{'border-0': !isOpen}"
        >
          <label
            class="sr-only"
            for="inlineFormInputGroup"
            v-html="$i18n('search.placeholder')"
          />
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">
                <i
                  class="fas"
                  :class="{
                    'fa-search': !isLoading,
                    'fa-spinner fa-spin': isLoading,
                  }"
                />
              </div>
            </div>
            <input
              id="inlineFormInputGroup"
              v-model="query"
              type="text"
              class="form-control"
              :placeholder="$i18n('search.placeholder')"
            >
            <div class="input-group-append">
              <div
                class="input-group-text is-clickable"
                @click="query=''"
              >
                <i
                  class="fas"
                  :class="{
                    'fa-times': query.length > 0,
                  }"
                />
              </div>
            </div>
          </div>
        </div>
        <div
          v-if="isOpen"
          class="modal-body"
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
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-sm btn-light"
            data-dismiss="modal"
            v-html="$i18n('globals.modal.close')"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SearchResults from './SearchResults'

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
    query (query, oldQuery) {
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
</style>
