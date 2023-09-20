<template>
  <div
    v-if="filteredThreads.length"
    class="found-threads"
  >
    <b-alert
      show
      class="duplicate-warning"
      variant="warning"
    >
      <i class="fas fa-exclamation-triangle" />
      {{ $i18n('forum.similar_threads_warning') }}
    </b-alert>
    <SearchResultEntry
      v-for="thread in filteredThreads"
      :key="thread.id"
      :href="$url('forum', groupId, subforumId, thread.id)"
      :title="thread.name"
      :teaser="getThreadDate(thread)"
      teaser-icon="far fa-clock"
    />
  </div>
</template>

<script>
import { searchForum } from '@/api/search'
import SearchResultEntry from '@/components/SearchBar/SearchResultEntry.vue'

export default {
  components: { SearchResultEntry },
  props: {
    groupId: {
      type: Number,
      required: true,
    },
    subforumId: {
      type: Number,
      required: true,
    },
    query: {
      type: String,
      default: '',
    },
  },
  data: () => ({
    threads: [],
  }),
  computed: {
    filteredThreads () {
      if (!this.query) return []
      const words = this.query.toLowerCase().match(/[^ ,;+.]+/g)
      return this.threads.filter(thread => words.every(word => thread.name.toLowerCase().includes(word)))
    },
  },
  methods: {
    getThreadDate (thread) {
      const lastUpdated = new Date(Date.parse(thread.teaser))
      return this.$dateFormatter.base(lastUpdated)
    },
    async fetch () {
      await this.$nextTick()
      const query = this.query
      await new Promise(resolve => window.setTimeout(resolve, 200))
      if (!query || query !== this.query) return
      this.threads = await searchForum(this.groupId, this.subforumId, this.query)
    },
  },
}
</script>

<style lang="scss" scoped>
.found-threads {
  border: 1px solid var(--fs-border-default);
  border-radius: 0 0 var(--border-radius) var(--border-radius);
  border-top: none;
  position: relative;
  top: calc(-1 * var(--border-radius));
  padding-top: var(--border-radius);
  max-height: 20em;
  overflow: scroll;
}

.duplicate-warning {
  margin: .5em;
}

</style>
