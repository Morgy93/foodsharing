<template>
  <a
    :href="$url('store', entry.id)"
    role="menuitem"
    class="dropdown-item dropdown-action"
  >
    <i
      v-b-tooltip="entry.pickupStatus > 0 ? $i18n('store.tooltip_'+['yellow', 'orange', 'red'][entry.pickupStatus - 1]) : ''"
      class="field-icon fas fa-circle"
      :class="{
        'text-white-50': entry.pickupStatus === 0,
        'text-primary': entry.pickupStatus === 1,
        'text-warning': entry.pickupStatus === 2,
        'text-danger': entry.pickupStatus === 3
      }"
    />
    <i
      v-if="entry.isManaging"
      v-b-tooltip="$i18n('store.tooltip_managing')"
      class="field-icon fas fa-users-cog text-muted"
    />
    {{ entry.name }}
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
.field-icon {
  color: currentColor;
  cursor: help;
}
</style>
