<template>
  <Link
    aria-label="foodsharing"
    class="foodsharing"
    :href="isLoggedIn ? $url('dashboard') : $url('home') "
  >
    <template
      v-if="viewIsMD && !small"
      #text
    >
      food<span class="part">shar<span class="apple">i</span>ng</span>
    </template>
    <template
      v-else
      #text
    >
      f<span class="part">s</span>
    </template>
  </Link>
</template>
<script>
// Store
import DataUser from '@/stores/user'
// Components
import Link from '@/components/Navigation/_NavItems/NavLink'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
export default {
  components: {
    Link,
  },
  mixins: [MediaQueryMixin],
  props: {
    small: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    isLoggedIn () {
      return DataUser.getters.isLoggedIn()
    },
  },
}
</script>
<style lang="scss" scoped>
::v-deep.foodsharing a {
  font-weight: unset;
  cursor: pointer;
}
::v-deep.foodsharing {
  font-family: var(--fs-font-family-headline);
  color: var(--fs-color-primary-500);
  font-size: 1.1rem;
  font-weight: normal;

  display: flex;
  align-items: center;
  justify-content: center;

  @media (max-width: 767px) {
    line-height: 0.8;
    min-width: auto;
  }

  .part {
    color: var(--fs-color-secondary-500);
  }
}

::v-deep.foodsharing .nav-link:hover {
  color: var(--fs-color-primary-700);

  .part {
    color: var(--fs-color-secondary-700);
  }

  & .apple::before {
    content: "♥";
    color: var(--fs-color-danger-500);
    position: absolute;
    font-size: 0.5em;
  }
}
</style>
