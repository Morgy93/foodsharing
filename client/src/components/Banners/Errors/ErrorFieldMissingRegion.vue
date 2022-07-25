<template>
  <div
    v-if="!hasHomeRegion && isFoodsaver"
    class="information-field alert alert-danger d-flex justify-content-between"
  >
    <i
      class="fas fa-map-marker-alt info-icon align-self-center mr-3 d-none d-md-block "
    />
    <div class="flex-grow-1 d-flex flex-column justify-content-between">
      <div class="alignt-self-start">
        <h4
          v-html="$i18n('error.choose_home_region.title')"
        />
        <p
          class="description mb-1 w-md-50"
          v-html="$i18n('error.choose_home_region.info')"
        />
      </div>
      <div
        class="d-flex align-items-center my-2"
      >
        <button
          class="btn btn-sm btn-danger font-weight-bold align-self-start mr-2"
          @click="open()"
          v-html="$i18n('error.choose_home_region.link')"
        />
      </div>
    </div>
  </div>
</template>

<script>
// Stores
import DataUser from '@/stores/user'
import DataRegions from '@/stores/regions'

export default {
  computed: {
    hasHomeRegion () {
      return DataUser.getters.hasHomeRegion()
    },
    isFoodsaver () {
      return DataUser.getters.isFoodsaver()
    },
  },
  watch: {
    hasHomeRegion: {
      handler (val) {
        if (!val && this.isFoodsaver) {
          this.open()
        }
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    open () {
      // console.log('open', DataRegions.getters.joinRegionModal.isShown())
      DataRegions.mutations.joinRegionModal.show()
    },
  },
}
</script>

<style lang="scss" scoped>
.information-field {
  min-height: 100px;
}

.alert-broadcast {
  min-height: 0;
  align-content: center;
}

.info-icon {
  font-size: 3rem;
}

.close-interaction {
  cursor: pointer;

  &:hover {
    color: var(--primary);
  }
}

.list-group-item-action {
  cursor: pointer;
}

::v-deep.description {
  a {
    text-decoration: underline;
  }
  & * {
      margin-bottom: 0 !important;
  }
}
</style>
