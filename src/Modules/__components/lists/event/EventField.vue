<template>
  <div class="list-group-item d-flex align-items-skretch">
    <div
      v-b-tooltip.hover="getDateTooltip()"
      class="event-item-date flex-column mr-2 text-center rounded default"
      :class="{'accept': status === 1, 'maybe': status === 2}"
      style="width: 5rem"
    >
      <span
        class="font-weight-bold"
        v-html="getMonth()"
      />
      <div
        class="d-flex flex-column bg-white justify-content-center rounded text-dark"
        style="min-height: 4rem; font-size: 1.25rem; font-family: 'Alfa Slab One', serif;"
      >
        <span
          v-if="isToday()"
          v-html="$i18n('date.Today')"
        />
        <span
          v-else-if="isSoon() === 1"
          class="small"
          v-html="$i18n('date.tomorrow')"
        />
        <span
          v-else-if="isSoon() < 3"
          v-html="getWeekDayString()"
        />
        <span
          v-else
          class="small"
          v-html="getDayString()"
        />
      </div>
    </div>
    <div class="d-flex flex-column flex-grow-1">
      <div class="d-flex flex-column mb-auto">
        <div class="d-flex justify-content-between align-items-sm-center">
          <a
            v-b-tooltip.hover="entry.name.length > 30 ? entry.name : null"
            :href="$url('event', entry.id)"
            class="event-item-headline font-weight-bold d-inline-block text-truncate"
            v-html="entry.name"
          />
          <small
            v-b-tooltip.hover="entry.regionName.length > 20 ? entry.regionName : null"
            class="event-item-region text-muted text-truncate"
          >
            {{ entry.regionName }}
          </small>
        </div>
        <div class="text-muted d-flex-inline align-items-center">
          <i class="fas fa-clock" />
          <span
            v-b-tooltip.hover="$i18n('events.duration', getTooltipDuration())"
          >
            {{ $i18n('events.span', getTimeSpan()) }}
          </span>
        </div>
      </div>
      <div class="list-group list-group-horizontal small text-center">
        <button
          class="list-group-item list-group-item-action"
          :class="{'accept': status === 1}"
          @click="acceptInvitation(entry.id); status = 1"
        >
          <i class="fas fa-calendar-check d-none d-sm-inline" />
          {{ $i18n('events.button.yes') }}
        </button>
        <button
          class="list-group-item list-group-item-action"
          :class="{'maybe': status === 2}"
          @click="maybeInvitation(entry.id); status = 2"
        >
          <i class="fas fa-question-circle d-none d-sm-inline" />
          {{ $i18n('events.button.maybe') }}
        </button>
        <button
          class="list-group-item list-group-item-action"
          :class="{'default': status === 3}"
          @click="declineInvitation(entry.id); status = 3"
        >
          <i class="fas fa-fw fa-calendar-times d-none d-sm-inline" />
          {{ $i18n('events.button.no') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import dateFnsLocaleDE from 'date-fns/locale/de'
import isSameDay from 'date-fns/isSameDay'
import formatDate from 'date-fns/format'
import differenceInDays from 'date-fns/differenceInDays'
import formatDistanceStrict from 'date-fns/formatDistanceStrict'
import { acceptInvitation, declineInvitation, maybeInvitation } from '@/api/events'

export default {
  props: {
    entry: { type: Object, default: () => {} },
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
    isToday (time = this.startDate) {
      return differenceInDays(time, Date.now()) === 0
    },
    isSoon (time = this.startDate) {
      return differenceInDays(time, Date.now())
    },
    getMonth (time = this.startDate) {
      const opt = {
        month: 'long',
      }
      return new Date(time).toLocaleString('de-DE', opt)
    },
    getWeekDayString (time = this.startDate) {
      const opt = {
        weekday: 'short',
      }
      return new Date(time).toLocaleString('de-DE', opt)
    },
    getDayString (time = this.startDate) {
      const opt = {
        weekday: 'short',
        day: 'numeric',
      }
      return new Date(time).toLocaleString('de-DE', opt)
    },
    getDateTooltip (start = this.startDate) {
      return formatDate(start, 'dd.MM.yyyy HH:mm') + ' (' + this.$dateDistanceInWords(start) + ')'
    },
    getTimeSpan (start = this.startDate, end = this.endDate) {
      if (isSameDay(end, start)) {
        return {
          from: formatDate(start, 'HH:mm'),
          until: formatDate(end, 'HH:mm'),
        }
      } else {
        return {
          from: formatDate(start, 'HH:mm'),
          until: formatDate(end, '[d.MM.] HH:mm'),
        }
      }
    },
    getTooltipDuration (start = this.startDate, end = this.endDate) {
      return { duration: formatDistanceStrict(end, start, { locale: dateFnsLocaleDE }) }
    },
  },
}
</script>

<style lang="scss" scoped>
.event-item-headline {
  max-width: 200px;
  font-size:1rem;

  @media (max-width: 500px) {
    max-width: auto;
  }
}

.event-item-region {
  max-width: 150px;

  @media (max-width: 500px) {
    display: none;
  }
}

.event-item-date {
  display: flex;

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

</style>
