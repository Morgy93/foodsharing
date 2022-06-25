<template>
  <a
    :href="href"
    class="d-flex dropdown-item text-truncate search-result"
  >
    <div
      v-if="image"
      class="icon img-thumbnail mr-2"
    >
      <img
        class="icon--autosize icon--rounded"
        :src="image"
        :alt="title"
      >
    </div>
    <div>
      <h6 class="m-0">{{ title }}</h6>
      <small
        v-if="teaser"
        class="text-muted text-truncate"
      >
        <i
          v-if="teaserIcon"
          :class="`fa-fw ${teaserIcon}`"
        />
        {{ teaser }}
      </small>
    </div>
  </a>
</template>
<script>
export default {
  props: {
    href: {
      type: String,
      default: null,
    },
    title: {
      type: String,
      default: '',
    },
    teaser: {
      type: String,
      default: '',
    },
    teaserIcon: {
      type: String,
      default: '',
    },
    image: {
      type: String,
      default: '',
    },
  },
}
</script>

<style lang="scss" scoped>
@import '../../scss/icon-sizes.scss';
</style>
