<template>
  <List
    v-if="list.length > 0"
    :tag="title"
    :title="$i18n(title)"
    :hide="defaultAmount > list.length"
    @show-full-list="showFullList"
    @reduce-list="reduceList"
  >
    <a
      v-for="(entry, key) in filteredList"
      :key="key"
      class="list-group-item list-group-item-action d-flex align-items-center"
      :href="getUrl(entry.id)"
    >
      <div class="img-thumbnail mr-2">
        <img
          :alt="$i18n('basket.by', {name: entry.fs_name})"
          :src="getImageUrl(entry.picture)"
          width="30"
          height="30"
        >
      </div>
      <div class="d-flex flex-column mb-auto justify-content-between text-truncate">
        <p
          v-b-tooltip="entry.description.length > 30 ? entry.description : ''"
          class="mb-0 text-truncate"
          v-html="entry.description"
        />
        <small
          v-b-tooltip.hover.html="$i18n('basket.expires', {date: new Date(entry.until).toLocaleString('de-DE', {
            month: 'long',
            day: 'numeric',
          })})"
          class="text-truncate"
          v-html="$i18n('basket.by', {name: entry.fs_name})"
        />
      </div>
    </a>
  </List>
</template>

<script>

import List from './_List.vue'
export default {
  components: {
    List,
  },
  props: {
    title: { type: String, default: 'basket.nearby' },
    list: { type: Array, default: () => [] },
  },
  data () {
    const defaultAmount = 5
    return {
      defaultAmount: defaultAmount,
      amount: defaultAmount,
    }
  },
  computed: {
    filteredList () {
      return this.list.slice(0, this.amount)
    },
  },
  methods: {
    showFullList () {
      this.amount = this.list.length
    },
    reduceList () {
      this.amount = this.defaultAmount
    },
    getImageUrl (picture) {
      if (picture) {
        return `/images/basket/thumb-${picture}`
      } else {
        return '/img/basket.png'
      }
    },
    getRelativeTime (time) {
      const difference = -Math.floor(Date.now() / 1000 - time)
      const formatter = new Intl.RelativeTimeFormat('de')
      if (difference < 3600) {
        return formatter.format(Math.floor(difference / 60), 'minutes')
      } else if (difference < 86400) {
        return formatter.format(Math.floor(difference / 3600), 'hours')
      } else if (difference < 2620800) {
        return formatter.format(Math.floor(difference / 86400), 'days')
      } else {
        return time
      }
    },
    getUrl (id) {
      return `/essenskoerbe/${id}`
    },
  },
}
</script>

<style lang="scss" scoped>
.list-group.pickup-list {
  margin-bottom: 1rem;
}

.list-group-item.showMore {
  padding: 0.5rem;
}

.list-group-item-action {
  cursor: pointer;
}
</style>
