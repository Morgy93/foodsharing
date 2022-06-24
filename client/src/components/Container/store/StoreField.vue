<template>
  <a
    class="list-group-item list-group-item-action field field--stack"
    :href="$url('store', entry.id)"
  >
    <div class="field-container">
      <h6
        v-b-tooltip="entry.name.length > 30 ? entry.name : ''"
        class="field-headline"
        v-html="entry.name"
      />
      <i
        v-if="entry.isManaging"
        v-b-tooltip="$i18n('store.tooltip_managing')"
        class="field-icon fas fa-users-cog text-muted"
      />
    </div>
    <div
      v-if="entry.pickupStatus > 0"
      class="d-flex align-items-center"
    >
      <i
        class="fas fa-circle mr-1"
        :class="{
          'text-primary': entry.pickupStatus === 1,
          'text-warning': entry.pickupStatus === 2,
          'text-danger': entry.pickupStatus === 3
        }"
      />
      <small
        class="field-subline"
        v-html="pickupStringStatus"
      />
    </div>
  </a>
</template>

<script>
export default {
  props: {
    entry: { type: Object, default: () => {} },
  },
  computed: {
    pickupStringStatus () {
      if (entry.pickupStatus > 0) {
        return $i18n('store.tooltip_'+['yellow', 'orange', 'red'][entry.pickupStatus - 1])
      }
      return ''
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
