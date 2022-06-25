<template>
  <a
    :href="$url('store', entry.id)"
    role="menuitem"
    class="dropdown-header dropdown-item d-flex justify-content-between align-items-center"
    :clss="classes"
  >
    <span class="d-flex align-items-center truncated dropdown-action">
      <i
        v-if="entry.isManaging"
        v-b-tooltip="$i18n('store.tooltip_managing')"
        class="store-entry--icon fas fa-users-cog"
        style="cursor: help;"
      />
      <span
        v-b-tooltip="entry.name.length > 30 ? entry.name : ''"
        class="d-inline-block text-truncate"
        v-html="entry.name"
      />
    </span>
    <i
      v-if="entry.pickupStatus > 0"
      v-b-tooltip="$i18n('store.tooltip_'+['yellow', 'orange', 'red'][entry.pickupStatus - 1])"
      class="fas fa-circle mr-1"
      :class="{
        'text-primary': entry.pickupStatus === 1,
        'text-warning': entry.pickupStatus === 2,
        'text-danger': entry.pickupStatus === 3
      }"
    />
  </a>
</template>

<script>

export default {
  props: {
    entry: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    classes () {
      return [
        'list-group-item',
        'list-group-item-action',
      ]
    },
    pickupStringStatus () {
      return 'store.tooltip_' + ['yellow', 'orange', 'red'][this.entry.pickupStatus - 1]
    },
  },
}
</script>

<style lang="scss" scoped>
.store-entry--icon {
  color: currentColor;
}

.truncated {
  flex: 1;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
