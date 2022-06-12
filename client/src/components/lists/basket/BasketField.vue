<template>
  <a
    class="list-group-item list-group-item-action field"
    :href="$url('basket', entry.id)"
  >
    <div class="img-thumbnail mr-2">
      <img
        :alt="$i18n('basket.by', {name: entry.fs_name})"
        :src="getImageUrl(entry.picture)"
        class="rounded"
        width="35"
        height="35"
      >
    </div>
    <div class="field-container field-container--stack">
      <strong
        v-b-tooltip="entry.description.length > 30 ? entry.description : ''"
        class="field-headline"
        v-html="entry.description"
      />
      <div class="field-container">
        <small
          v-b-tooltip.html="$i18n('basket.by', {name: entry.fs_name})"
          class="field-subline field-subline--muted"
          v-html="$i18n('basket.expires', {date: dateFormat(new Date(entry.until), 'full-short')})"
        />
        <span
          v-if="entry.distance"
          class="ml-2 badge list-group-item-dark badge-pill"
        >
          <i
            v-if="distanceNumber(entry.distance) > 0"
            class="fas fa-directions"
          />
          {{ distanceString(entry.distance) }}
        </span>
      </div>
    </div>
  </a>
</template>

<script>
import DateFormatterMixin from '@/mixins/DateFormatterMixin'

export default {
  mixins: [DateFormatterMixin],
  props: {
    entry: { type: Object, default: () => {} },
  },
  methods: {
    distanceNumber (num) {
      return Math.round(num * 1000) / 1000
    },
    distanceString (num) {
      num = this.distanceNumber(num)
      if (num === 0) {
        return '💑'
      } else if (num < 1) {
        return `${(num * 1000).toLocaleString()} m`
      } else {
        return `${(num).toFixed(1).toLocaleString()} km`
      }
    },
    getImageUrl (picture) {
      if (picture) {
        return `/images/basket/thumb-${picture}`
      } else {
        return '/img/basket.png'
      }
    },
  },
}
</script>
