<template>
  <span
    aria-label="foodsharing"
    class="foodsharing"
  >
    <span
      v-if="viewIsMD && !small"
      class="logo-text"
    >
      food<span class="text-secondary">shar<span class="apple">i</span>ng</span>
    </span>
    <span
      v-else
    >
      f<span class="text-secondary">s</span>
    </span>
  </span>
</template>
<script>
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  mixins: [MediaQueryMixin],
  props: {
    small: {
      type: Boolean,
      default: false,
    },
  },
}
</script>
<style lang="scss" scoped>
.foodsharing {
    font-family: 'Alfa Slab One',serif;
    color: currentColor;
    font-size: 1.1rem;

    display: flex;
    align-items: center;
    justify-content: center;

    @media (max-width: 330px) {
      min-width: auto;
    }

    span.logo-text:hover .apple::before {
      content: '♥';
      color: red;
      position: absolute;
      font-size: 0.5em;
    }

    &:hover {
      text-decoration: none;
    }
}
</style>
