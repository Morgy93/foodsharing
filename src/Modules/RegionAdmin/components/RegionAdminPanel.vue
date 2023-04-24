<template>
  <div :class="{disabledLoading: isLoading}">
    <b-overlay :show="isLoading">
      <template #overlay>
        <i class="fas fa-spinner fa-spin" />
      </template>
    </b-overlay>

    <div class="row">
      <region-tree
        class="col-4"
        @change="onRegionSelected"
      />
      <region-admin-map
        :store-markers.sync="storeMarkers"
        class="col-8"
      />
    </div>
    <region-form
      :region-details.sync="regionDetails"
      class="mt-5"
    />
  </div>
</template>

<script>

import RegionAdminMap from './RegionAdminMap'
import RegionForm from './RegionForm'
import RegionTree from '@/components/regiontree/RegionTree'
import { getRegionDetails } from '@/api/regions'
import { pulseError } from '@/script'
import { BOverlay } from 'bootstrap-vue'

export default {
  components: { RegionAdminMap, RegionForm, RegionTree, BOverlay },
  props: {
    regionId: { type: Number, default: null },
  },
  data () {
    return {
      isLoading: false,
      regionDetails: {},
    }
  },
  computed: {
    storeMarkers () {
      return (this.regionDetails.storeMarkers !== undefined) ? this.regionDetails.storeMarkers : []
    },
  },
  methods: {
    async onRegionSelected (region) {
      this.isLoading = true

      try {
        this.regionDetails = await getRegionDetails(region.id)
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
      }

      this.isLoading = false
    },
  },
}
</script>
