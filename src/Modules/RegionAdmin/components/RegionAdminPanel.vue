<template>
  <div>
    <p>{{ regionDetails.id }}, {{ regionDetails.name }}</p>
    <region-admin-map
      :store-markers.sync="storeMarkers"
    />
    <region-tree
      @change="onRegionSelected"
    />
  </div>
</template>

<script>

import RegionAdminMap from './RegionAdminMap'
import RegionTree from '@/components/regiontree/RegionTree'
import { getRegionDetails } from '@/api/regions'

export default {
  components: { RegionAdminMap, RegionTree },
  props: {
    regionId: { type: Number, default: null },
  },
  data () {
    return {
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
      console.error(region.id + ', ' + region.name)
      this.regionDetails = await getRegionDetails(region.id)
    },
  },
}
</script>
