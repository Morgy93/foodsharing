<template>
  <a
    :href="href"
    aria-label="foodsharing"
    class="navbar-brand brand"
  >
    <span
      v-if="viewIsMD"
      class="logo-text"
    >
      food<span class="text-secondary">shar<span class="apple">i</span>ng</span>
    </span>
    <span
      v-else
      class="text-primary"
    >
      f<span class="text-secondary">s</span>
    </span>
  </a>
</template>
<script>
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  mixins: [MediaQueryMixin],
  props: {
    href: {
      type: String,
      default: '',
    },
  },
}
</script>
<style lang="scss" scoped>
.brand {
    margin: 0;
    font-family: 'Alfa Slab One',serif;
    color: var(--primary);
    font-size: 1.1rem;

    @media (max-width: 330px) {
      min-width: auto;
    }

    span.logo-text:hover .apple::before {
      content: '♥';
      color: red;
      position: absolute;
      font-size: 0.5em;
    }
}
</style>
