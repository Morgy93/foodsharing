<template>
  <ul class="p-0 m-0">
    <ActivityPost
      v-for="(post, index) in hideUnwanted(updates)"
      :key="index"
      v-bind="post"
    />
    <infinite-loading
      ref="infiniteLoading"
      spinner="waveDots"
      @infinite="infiniteHandler"
    >
      <li
        slot="no-results"
        class="list-group-item"
      >
        <span>
          {{ $i18n('dashboard.no_updates') }}
        </span>
      </li>
      <li
        slot="no-more"
        class="list-group-item"
      >
        <span>
          {{ $i18n('dashboard.no_more_updates_' + activeType) }}
        </span>
      </li>
    </infinite-loading>
  </ul>
</template>

<script>
import { getUpdates } from '@/api/dashboard'
import ActivityPost from './ActivityPost'
import InfiniteLoading from 'vue-infinite-loading'
import { parseISO, compareDesc } from 'date-fns'

export default {
  components: { ActivityPost, InfiniteLoading },
  props: {
    displayedType: {
      type: String,
      default: 'all',
    },
  },
  data () {
    return {
      updates: [],
      page: 0,
    }
  },
  computed: {
    activeType () {
      return this.displayedType
    },
  },

  methods: {
    resetInfinity () {
      // from https://github.com/PeachScript/vue-infinite-loading/issues/123#issuecomment-357129636
      // this causes the loader to start looking for data again, when in completed state
      this.$refs.infiniteLoading.stateChanger.reset()
    },
    hideUnwanted (updates) {
      if (this.activeType === 'all') {
        return updates
      }
      return updates.filter(a => this.activeType === a.type)
    },
    async infiniteHandler ($state) {
      const updates = await getUpdates(this.page)
      updates.forEach(function (u, index, array) {
        array[index].time = parseISO(array[index].time)
      })
      const filtered = this.hideUnwanted(updates)
      if (filtered.length) {
        this.page += 1
        updates.sort((a, b) => {
          return compareDesc(a.time, b.time)
        })
        this.updates.push(...updates)
        $state.loaded()
      } else {
        $state.loaded()
        $state.complete()
      }
    },
    async reloadData () {
      this.resetInfinity()
      this.page = 0
      this.updates = []
    },
  },
}
</script>
