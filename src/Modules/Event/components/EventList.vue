<template>
  <div>
    <div class="card mb-3">
      <div class="card-header text-white bg-primary">
        {{ $i18n('events.bread') }}
      </div>
      <div
        v-for="event in currentEvents"
        :key="event.id"
      >
        <EventPanel
          :event-id="event.id"
          :start="event.start"
          :end="event.end"
          :title="event.name"
          :status="-1"
        />
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header text-white bg-primary">
        {{ $i18n('events.past') }}
      </div>
      <div class="card-body">
        <div class="form-row p-1 mb-2">
          <label
            for="filter-input"
            class="col-form-label col-form-label-sm"
          >
            {{ $i18n('filter_by') }}
          </label>
          <b-form-input
            id="filter-input"
            v-model="filterText"
            type="text"
            class="form-control form-control-sm col-8"
            :placeholder="$i18n('name')"
          />
        </div>
        <div
          v-for="event in endedEvents"
          :key="event.id"
        >
          <EventPanel
            :event-id="event.id"
            :start="event.start"
            :end="event.end"
            :title="event.name"
            :status="-1"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { optimizedCompare } from '@/utils'
import EventPanel from './EventPanel'

export default {
  components: { EventPanel },
  props: {
    events: {
      type: Array,
      default: () => [],
    },
  },
  data () {
    return {
      currentPage: 1,
      perPage: 20,
      filterText: null,
    }
  },
  computed: {
    currentEvents: function () {
      return this.events.filter(p => !this.isEventInPast(p))
    },
    endedEvents: function () {
      // select events that ended in the past, filter by name, and sort as new-to-old
      let filtered = this.events.filter(p => this.isEventInPast(p))

      const filterText = this.filterText ? this.filterText.toLowerCase() : null
      if (filterText) {
        filtered = filtered.filter(p => p.name.toLowerCase().indexOf(filterText) !== -1)
      }

      return filtered.sort((a, b) => {
        const aD = this.convertDate(a.start)
        const bD = this.convertDate(b.start)
        if (aD.getTime() === bD.getTime()) return 0
        return aD > bD ? 1 : -1
      })
    },
  },
  methods: {
    compare: optimizedCompare,
    isEventInPast (event) {
      return this.convertDate(event.end) < new Date()
    },
    convertDate (date) {
      return new Date(Date.parse(date))
    },
  },
}
</script>
