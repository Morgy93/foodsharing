<template>
  <a
    :href="linkUrl"
    :aria-label="$i18n('home.title')"
    class="navbar-brand brand"
  >
    <span
      v-if="!viewIsSMmin"
      class="logo-text"
    >
      food<span class="green">shar<span class="apple">i</span>ng</span>
    </span>
    <span
      v-else
      class="logo-text"
    >
      f<span class="green">s</span>
    </span>
  </a>
</template>
<script>
import MediaQueryMixin from '@/utils/MediaQueryMixin'

export default {
  mixins: [MediaQueryMixin],
  props: {
    linkUrl: {
      type: String,
      default: '',
    },
  },
}
</script>
<style lang="scss" scoped>
.brand {
    min-width: 45px;
    margin: 0;
    font-family: 'Alfa Slab One',serif;
    color: var(--primary);
    font-size: 1.1rem;

    @media (max-width: 330px) {
      min-width: auto;
    }

    span.green {
        color: #64ae25;
        position: relative;
    }
    span.logo-text:hover .apple::before {
      content: '♥';
      color: red;
      position: absolute;
      font-size: 0.5em;
    }
}
</style>
