<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div class="store-view bootstrap">
    <div class="card rounded mb-2">
      <div
        class="card-header text-white bg-primary d-flex justify-content-between"
        @click.prevent="toggleWallDisplay"
      >
        {{ $i18n('wall.name') }}

        <a
          class="px-1 text-light"
          href="#"
          @click.prevent.stop="toggleWallDisplay"
        >
          <i :class="['fas fa-fw', `fa-chevron-${displayWall ? 'down' : 'left'}`]" />
        </a>
      </div>

      <div v-show="displayWall" class="card-body p-0">
        <StoreWall
          :store-id="storeId"
          :show-only-excerpt="viewIsMobile"
          :managers="storeManagers"
          :may-write-post="mayWritePost"
          :may-delete-everything="mayDeleteEverything"
        />
      </div>
    </div>
  </div>
</template>

<script>
import StoreWall from './StoreWall'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: { StoreWall },
  mixins: [MediaQueryMixin],
  props: {
    storeId: { type: Number, required: true },
    storeManagers: { type: Array, default: () => [] },
    mayWritePost: { type: Boolean, required: true },
    mayDeleteEverything: { type: Boolean, required: true },
    expandWallByDefault: { type: Boolean, default: true },
  },
  data () {
    return {
      displayWall: this.expandWallByDefault,
    }
  },
  methods: {
    toggleWallDisplay () {
      this.displayWall = !this.displayWall
    },
  },
}
</script>

<style lang="scss" scoped>
</style>
