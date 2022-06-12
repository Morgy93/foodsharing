<template>
  <a
    class="list-group-item list-group-item-action field field--stack"
    :href="$url('store', entry.store_id)"
  >
    <div class="field-container">
      <small
        v-b-tooltip="entry.store_name.length > 30 ? entry.store_name : ''"
        class="field-subline"
        v-html="entry.store_name"
      />
      <span
        v-b-tooltip="!entry.confirmed ? $i18n('pickup.to_be_confirmed') : ''"
        class="badge badge-pill d-flex p-1 align-items-center"
        :class="{
          'badge-danger': !entry.confirmed,
          'badge-success': entry.confirmed,
        }"
      >
        <i
          class="fas"
          :class="{
            'fa-check-circle': entry.confirmed,
            'fa-clock': !entry.confirmed,
          }"
        />
        <span
          v-if="entry.max_fetchers > 1"
          class="ml-1 mr-1"
        >
          {{ entry.slot_confimations.split(",").filter(e=>e==='1').length }} / {{ entry.max_fetchers }}
        </span>
      </span>
    </div>
    <strong
      class="field-headline field-headline--big"
      :class="{
        'text-danger': getHourDifferenceToNow(entry.timestamp * 1000) < 24,
        'text-black-50': !getHourDifferenceToNow(entry.timestamp * 1000) > 24
      }"
    >
      <i class="fas fa-clock mr-2" />
      <span
        v-if="getHourDifferenceToNow(entry.timestamp * 1000) < 24"
        v-b-tooltip="dateFormat(entry.timestamp * 1000)"
        v-html="dateDistanceInWords(entry.timestamp * 1000)"
      />
      <span
        v-else
        v-html="dateFormat(entry.timestamp * 1000, 'full-short')"
      />
    </strong>
  </a>
</template>

<script>
import DateFormatterMixin from '@/mixins/DateFormatterMixin'

export default {
  mixins: [DateFormatterMixin],
  props: {
    entry: { type: Object, default: () => {} },
  },
}
</script>
