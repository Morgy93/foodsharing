<template>
  <li class="nav-item">
    <a
      class="nav-link"
      :href="href"
      role="button"
    >
      <slot name="icon">
        <i
          v-if="icon"
          class="nav-icon fas"
          :class="icon"
        />
      </slot>
      <slot name="badge">
        <span
          v-if="badge"
          class="badge badge-danger"
          v-html="badge"
        />
      </slot>
      <slot name="text">
        <span
          class="nav-text"
          v-html="title"
        />
      </slot>
    </a>
  </li>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: '',
    },
    href: {
      type: String,
      default: '#',
    },
    icon: {
      type: String,
      default: undefined,
    },
    badge: {
      type: [String, Number],
      default: 0,
    },
  },
}
</script>

<style lang="scss" scoped>
@import "./NavItems.scss";
</style>
