<template>
  <a
    :href="$url('store', entry.id)"
    role="menuitem"
    class="dropdown-item"
    :clss="classes"
  >
    <div class="d-flex mb-auto justify-content-between align-items-center">
      <div class="d-flex text-truncate">
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
        </div>
        <h5
          v-b-tooltip="entry.name.length > 30 ? entry.name : ''"
          class="d-inline-block text-truncate"
          v-html="entry.name"
        />
      </div>
      <i
        v-if="entry.isManaging"
        class="fas fa-users-cog text-muted"
        style="cursor: help;"
      />
    </div>
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
.dropdown-item {
    padding: 0.8em 1em;
    border: unset;

    h5 {
      font-weight: bold;
      font-size: 0.9em;
          margin: 0;
    }

    p {
        font-size: 0.8em;
    }
}

.truncated {
  flex: 1;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
