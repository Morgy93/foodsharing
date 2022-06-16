<template>
  <a
    class="list-group-item list-group-item-action"
    :href="$url('event', entry.id)"
  >
    <div class="d-flex">
      <div
        v-b-tooltip.hover="getDistanceTooltip(startDate)"
        class="event-item-date flex-column mr-2 text-center rounded default"
        :class="{'accept': status === 1, 'maybe': status === 2}"
      >
        <span
          class="font-weight-bold"
          v-html="dateFormat(startDate, 'month-long')"
        />
        <div class="event-item-date-container d-flex flex-column bg-white justify-content-center text-dark">
          <span
            v-if="isToday(startDate)"
            v-html="$i18n('date.Today')"
          />
          <span
            v-else-if="isTomorrow(startDate)"
            class="small"
            v-html="$i18n('date.-- Tomorrow')"
          />
          <span
            v-else-if="getDayDifferenceToNow(startDate) < 3"
            v-html="dateFormat(startDate, 'weekday-short')"
          />
          <span
            v-else
            class="small"
            v-html="dateFormat(startDate, 'day-weekday-short')"
          />
        </div>
      </div>
      <div class="d-flex justify-content-between flex-column truncated">
        <div>
          <h6
            v-b-tooltip.hover="entry.name.length > 30 ? entry.name : null"
            class="field-headline m-0 text-truncate"
            v-html="entry.name"
          />
          <span
            :href="$url('forum', entry.region_id)"
            class="d-block small text-muted text-truncate"
            v-html="entry.regionName"
          />
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div class="text-muted mt-auto">
            <i class="fas fa-clock" />
            <span
              v-b-tooltip.hover="$i18n('events.duration', getDurationToolTip(startDate, endDate))"
              v-html="$i18n('events.span', getTimeSpanToolTip(startDate, endDate))"
            />
          </div>
          <a
            v-if="!options"
            class="d-none d-sm-block small"
            :href="$url('event', entry.id)"
          >
            <span>
              {{ $i18n('events.button.change') }} ({{ $i18n('events.button.' + ['yes', 'maybe', 'no'][status - 1]) }})
            </span>
          </a>
        </div>
      </div>
    </div>
    <div
      v-if="options"
      class="list-group list-group-horizontal mt-2 small text-center"
    >
      <button
        class="list-group-item list-row-item list-group-item-action"
        :class="{'accept': status === 1}"
        @click.prevent="acceptInvitation(entry.id); status = 1"
      >
        <i class="fas fa-calendar-check d-none d-sm-inline" />
        {{ $i18n('events.button.yes') }}
      </button>
      <button
        class="list-group-item list-row-item list-group-item-action"
        :class="{'maybe': status === 2}"
        @click.prevent="maybeInvitation(entry.id); status = 2"
      >
        <i class="fas fa-question-circle d-none d-sm-inline" />
        {{ $i18n('events.button.maybe') }}
      </button>
      <button
        class="list-group-item list-row-item list-group-item-action"
        :class="{'default': status === 3}"
        @click.prevent="declineInvitation(entry.id); status = 3"
      >
        <i class="fas fa-fw fa-calendar-times d-none d-sm-inline" />
        {{ $i18n('events.button.no') }}
      </button>
    </div>
  </a>
</template>

<script>
import { acceptInvitation, declineInvitation, maybeInvitation } from '@/api/events'
import DateFormatterMixin from '@/mixins/DateFormatterMixin'

export default {
  mixins: [DateFormatterMixin],
  props: {
    entry: { type: Object, default: () => {} },
    options: { type: Boolean, default: false },
  },
  data () {
    return {
      startDate: new Date(this.entry.start_ts * 1000),
      endDate: new Date(this.entry.end_ts * 1000),
      status: this.entry.status,
    }
  },
  methods: {
    acceptInvitation,
    maybeInvitation,
    declineInvitation,
  },
}
</script>

<style lang="scss" scoped>
.event-item-date-container {
  border-radius: 0 0 var(--border-radius) var(--border-radius);
  min-height: 3rem;
  font-size: 1.15rem;
  font-family: 'Alfa Slab One', serif;
}

.event-item-date {
  display: flex;
  width: 5rem;

  @media (max-width: 320px) {
    display: none;
  }

  &.accept {
    border-color: var(--secondary);
  }

  &.maybe {
    border-color: var(--warning);
  }

  &.decline  {
    border-color: var(--gray);
  }
}

.small .list-group-item {
  padding: 0.5rem 0;

  &:first-child {
    border-right-width: 0;
  }

  &:not(:first-child):not(:last-child) {
    border-left-width: 0;
    border-right-width: 0;
  }
}

.default {
  z-index: 1;
  color: var(--white);
  background-color: var(--gray);
  border: 1px solid var(--gray);
}

.accept,
.accept:focus {
  z-index: 2;
  color: var(--white);
  background-color: var(--secondary);
}

.maybe,
.maybe:focus {
  z-index: 3;
  color: var(--dark);
  background-color: var(--warning);
}

.decline,
.decline:focus {
  z-index: 1;
  background-color: var(--gray);
}

.truncated {
  flex: 1;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

</style>
