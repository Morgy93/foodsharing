<template>
  <b-nav-item
    v-b-tooltip.hover.bottom
    class="menu-item"
    :href="url"
    :title="title"
    :aria-label="title"
    @click="$emit('click')"
  >
    <i :class="`fas ${icon}`" />
    <span
      v-if="badge"
      class="badge badge-danger"
      v-html="badge"
    />
    <span
      class="headline"
      :class="{'d-sm-inline-block': showTitle}"
    >
      {{ title }}
    </span>
  </b-nav-item>
</template>

<script>
import { VBTooltip } from 'bootstrap-vue'

export default {
  directives: { VBTooltip },
  props: {
    url: { type: String, default: undefined },
    icon: { type: String, default: undefined },
    title: { type: String, default: undefined },
    showTitle: { type: Boolean, default: false },
    badge: { type: [String, Number], default: undefined },
  },
}
</script>

<style lang="scss" scoped>
::v-deep.menu-item a {
  position: relative;
}

.headline {
  display: none;

  .collapse.show & {
    display: inline-block;
  }
}

.badge {
  position: absolute;
  top: 0;
  left: 20px;

  .collapse.show & {
    left: 10px;
  }
}
</style>
