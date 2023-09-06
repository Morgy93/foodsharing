<template>
  <b-dropdown
    v-if="activeOptions.length"
    no-caret
    right
    variant="badge-light"
    class="overflow-menu"
  >
    <template #button-content>
      <i class="fas fa-ellipsis-v" />
    </template>
    <b-dropdown-item
      v-for="(option, i) in activeOptions"
      :key="i"
      @click.stop="option.callback"
    >
      <i :class="`fas fa-${option.icon}`" />
      {{ $i18n(option.textKey) }}
    </b-dropdown-item>
  </b-dropdown>
</template>

<script>
export default {
  props: {
    options: {
      type: Array,
      default: () => [],
    },
  },
  computed: {
    activeOptions () {
      return this.options.filter(option => !option.hide)
    },
  },
}
</script>

<style lang="scss" scoped>
.overflow-menu {
  float: right;
  color: white;

  ::v-deep .btn {
    padding: 0.25em .75em;
    margin: -.25em 0;
    color: inherit;

    i {
      transition: transform .1s;
    }
    &:hover i {
      transform: scale(1.3);
    }

    &:focus {
      box-shadow: none !important;
    }
  }
}
</style>
