import dateFnsLocaleDE from 'date-fns/locale/de'
import isSameDay from 'date-fns/isSameDay'
import isSameYear from 'date-fns/isSameYear'
import formatDate from 'date-fns/format'
import isToday from 'date-fns/isToday'
import isTomorrow from 'date-fns/isTomorrow'
import differenceInDays from 'date-fns/differenceInDays'
import differenceInHours from 'date-fns/differenceInHours'
import formatDistanceStrict from 'date-fns/formatDistanceStrict'
import addDays from 'date-fns/addDays'
import parseISO from 'date-fns/parseISO'

import TimeAgo from 'javascript-time-ago'

// English.
import de from 'javascript-time-ago/locale/de'
import en from 'javascript-time-ago/locale/en'

TimeAgo.addDefaultLocale(en)
TimeAgo.addLocale(de)

// "de" language will be used, as it's the first one to match.
const timeAgo = new TimeAgo(['ru-RU', 'de-DE', 'en-US'])

// https://date-fns.org/docs/Getting-Started

export default {
  methods: {
    parseISO,
    isSameDay,
    formatDate,
    isTomorrow,
    isToday,
    differenceInDays,
    differenceInHours,
    dateFormat (date, format = 'full-long') {
      try {
        switch (format) {
          case 'day-weekday-short':
            return this.dateFormat(date, 'EE d')
          case 'weekday-short':
            return this.dateFormat(date, 'EE')
          case 'weekday-long':
            return this.dateFormat(date, 'EEEE')
          case 'month-short':
            return this.dateFormat(date, 'LLL')
          case 'month-long':
            return this.dateFormat(date, 'LLLL')
          case 'weekday-month-short':
            return this.dateFormat(date, 'EE LLL')
          case 'day':
            return this.dateFormat(date, 'd.M.yyyy')
          case 'time':
            return this.dateFormat(date, 'HH:mm')
          case 'full-long':
            if (isSameDay(date, new Date())) {
              return this.dateFormat(date, `'${this.$i18n('calendar.labelToday')}', cccc, HH:mm '${this.$i18n('date.clock')}'`)
              // this should render the format: date, heute, cccc, HH:mm Uhr
            } else if (isSameDay(date, addDays(new Date(), 1))) {
              return this.dateFormat(date, `'${this.$i18n('date.tomorrow')}', cccc, HH:mm '${this.$i18n('date.clock')}'`)
            } else if (isSameYear(date, new Date())) {
              return this.dateFormat(date, `cccc, do MMM, HH:mm '${this.$i18n('date.clock')}'`)
            } else {
              return this.dateFormat(date, `cccccc, do MMM yyyy, HH:mm '${this.$i18n('date.clock')}'`)
            }
          case 'full-short':
            if (isSameYear(date, new Date())) {
              return this.dateFormat(date, 'cccccc, d. MMM, HH:mm')
            } else {
              return this.dateFormat(date, 'cccccc, d.M.yyyy, HH:mm')
            }
          default:
            return formatDate(date, format, { locale: dateFnsLocaleDE })
        }
      } catch (error) {
        console.error({ error, date })
      }
    },
    relativeTime (date) { // https://gitlab.com/catamphetamine/javascript-time-ago#readme
      return timeAgo.format(date, 'mini-minute-now')
    },
    dateDistanceInWords (date, short = false) {
      const now = new Date()
      if (short) {
        if (isSameDay(date, now)) {
          return formatDate(date, 'HH:mm')
        }
        if (isSameDay(date, addDays(now, -1))) {
          return this.$i18n('date.yesterday')
        }
      }
      return formatDistanceStrict(date, now, {
        locale: dateFnsLocaleDE,
        addSuffix: true,
      })
    },
    getHourDifferenceToNow (date) {
      return differenceInHours(date, Date.now())
    },
    getDayDifferenceToNow (date) {
      return differenceInDays(date, Date.now())
    },
    displayedDuration (start, end) {
      return formatDistanceStrict(end, start, { locale: dateFnsLocaleDE })
    },
    displayedStart (date) {
      return formatDate(date, 'HH:mm')
    },
    displayedEnd (start, end) {
      if (isSameDay(start, end)) {
        return formatDate(end, 'HH:mm')
      } else {
        return formatDate(end, '[d.MM.] HH:mm')
      }
    },
    getDistanceTooltip (date) {
      return formatDate(date, 'dd.MM.yyyy HH:mm') + ' (' + this.$dateDistanceInWords(date) + ')'
    },
    getTimeSpanToolTip (start, end) {
      return {
        from: this.displayedStart(start),
        until: this.displayedEnd(start, end),
      }
    },
    getDurationToolTip (start, end) {
      return { duration: formatDistanceStrict(end, start, { locale: dateFnsLocaleDE }) }
    },
    today () {
      return this.$i18n('date.Today')
    },
    tomorrow () {
      return this.$i18n('date.-- Tomorrow')
    },
  },
}
