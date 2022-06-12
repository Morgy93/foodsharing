<template>
  <b-nav-item
    v-b-tooltip="!showTitle ? title : ''"
    class="item"
    :href="url"
    :title="title"
    :aria-label="title"
    @click="$emit('click')"
  >
    <i
      class="icon"
      :class="`fas ${icon}`"
    />
    <span
      v-if="badge"
      class="badge badge-danger"
      v-html="badge"
    />
    <span
      class="headline"
      :class="{'show': showTitle}"
      v-html="title"
    />
  </b-nav-item>
</template>

<script>

export default {
  props: {
    url: {
      type: String,
      default: undefined,
    },
    icon: {
      type: String,
      default: undefined,
    },
    title: {
      type: String,
      default: undefined,
    },
    badge: {
      type: [String, Number],
      default: undefined,
    },
    showTitle: {
      type: Boolean,
      default: false,
    },
  },
}
</script>

<style lang="scss" scoped>
.item {
  text-align: center;
}

::v-deep .nav-link{
  position: relative;
}

.headline,
.icon {
  color: var(--primary);
}

.headline {
  display: none;

  .collapse.show &,
  &.show {
    display: inline-block;
  }
}
</style>
