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
        v-text="$i18n('search.placeholder')"
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
        v-if="showResults"
        class="results"
        :results="results"
        :is-loading="isLoading"
        @close="$refs.searchBarModal.hide"
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
import SearchResults from '@/components/SearchBar/SearchResults'
import { instantSearch } from '@/api/search'
export default {
  components: { SearchResults },
  data () {
    return {
      query: '',
      showResults: false,
      isLoading: false,
      results: undefined,
    }
  },
  watch: {
    query (query) {
      // Require at least one word of length 3 or two of length 2:
      const queryLengthScore = query.split(/[,;+.\s]+/g).map(word => word.length - 1).reduce((a, b) => a + b)
      if (queryLengthScore > 1) {
        this.showResults = true
        this.delayedFetch()
        return
      }
      clearTimeout(this.timeout)
      this.showResults = false
      this.isLoading = false
      this.results = undefined
    },
  },
  methods: {
    focusSearchbar () {
      this.$refs.searchField.focus()
    },
    delayedFetch () {
      this.isLoading = true
      if (this.timeout) {
        clearTimeout(this.timeout)
      }
      this.timeout = setTimeout(() => {
        this.fetch()
      }, 200)
    },
    async fetch () {
      const curQuery = this.query
      const results = await instantSearch(curQuery)
      if (curQuery !== this.query) {
        // query has changed, throw away this response
        return false
      }
      this.results = results
      this.isLoading = false
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

::v-deep.results > .entry > .dropdown-item,
::v-deep.results > .entry > .dropdown-header {
  padding-left: 0;
  padding-right: 0;
}

</style>
