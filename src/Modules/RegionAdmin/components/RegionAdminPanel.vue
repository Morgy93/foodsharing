<template>
  <div :class="{disabledLoading: isLoading}">
    <b-overlay :show="isLoading">
      <template #overlay>
        <i class="fas fa-spinner fa-spin" />
      </template>
    </b-overlay>

    <div class="row">
      <region-tree
        ref="regionTree"
        class="col-4"
        @change="onRegionSelected"
      />
      <region-admin-map
        :store-markers.sync="storeMarkers"
        class="col-8"
      />
    </div>
    <button
      id="addRegionButton"
      class="btn btn-secondary mt-3"
      :disabled="regionDetails.id === undefined"
      @click="addNewRegion"
      v-text="$i18n('region.new')"
    />
    <button
      id="deleteRegionButton"
      class="btn btn-secondary mt-3"
      :disabled="regionDetails.id === undefined"
      @click="deleteRegion"
      v-text="$i18n('region.delete')"
    />
    <region-form
      :region-details.sync="regionDetails"
      class="mt-3"
    />
  </div>
</template>

<script>

import RegionAdminMap from './RegionAdminMap'
import RegionForm from './RegionForm'
import RegionTree from '@/components/regiontree/RegionTree'
import { addRegion, getRegionDetails } from '@/api/regions'
import { pulseError } from '@/script'
import { BOverlay } from 'bootstrap-vue'
import { deleteGroup } from '@/api/groups'

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
    /**
     * Callback from the region tree. Loads the selected region's details and forwards them to the form.
     */
    async onRegionSelected (region) {
      this.isLoading = true

      try {
        this.regionDetails = await getRegionDetails(region.id)
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
      }

      this.isLoading = false
    },
    /**
     * Handler for the 'new region' button. Shows a confirmation modal, adds a region, and selects that new region.
     */
    async addNewRegion () {
      const accepted = await this.$bvModal.msgBoxConfirm(this.$i18n('region.new_region_confirm', { parent: this.regionDetails.name }), {
        modalClass: 'bootstrap',
        title: this.$i18n('region.new'),
        cancelTitle: this.$i18n('button.cancel'),
        okTitle: this.$i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (accepted) {
        this.isLoading = true

        try {
          const child = await addRegion(this.regionDetails.id)

          // refresh the parent region's children to load the new region
          await this.$refs.regionTree.updateSelectedNode(child.id)
          this.$refs.regionTree.selectRegion(child.id)
        } catch (e) {
          pulseError(this.$i18n('error_unexpected'))
        }

        this.isLoading = false
      }
    },
    async deleteRegion () {
      const accepted = await this.$bvModal.msgBoxConfirm(this.$i18n('region.delete_confirm', { parent: this.regionDetails.name }), {
        modalClass: 'bootstrap',
        title: this.$i18n('region.delete'),
        cancelTitle: this.$i18n('button.cancel'),
        okTitle: this.$i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (accepted) {
        this.isLoading = true

        try {
          await deleteGroup(this.regionDetails.id)

          // refresh the parent region's children to remove the deleted node
          this.$refs.regionTree.selectRegion(this.regionDetails.parentId)
          await this.$refs.regionTree.updateSelectedNode(this.regionDetails.parentId)
        } catch (e) {
          if (e.code === 409) {
            pulseError(this.$i18n('region.delete_conflict'))
          } else {
            pulseError(this.$i18n('error_unexpected'))
          }
        }

        this.isLoading = false
      }
    },
  },
}
</script>
