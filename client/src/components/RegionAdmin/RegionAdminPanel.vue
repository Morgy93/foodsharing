<template>
  <div :class="{disabledLoading: isLoading}">
    <b-overlay :show="isLoading">
      <template #overlay>
        <i class="fas fa-spinner fa-spin" />
      </template>
    </b-overlay>

    <div class="row mb-2">
      <button
        id="addRegionButton"
        class="btn btn-secondary"
        :disabled="regionDetails.id === undefined"
        @click="addNewRegion"
        v-text="$i18n('region.new')"
      />
      <button
        id="deleteRegionButton"
        class="btn btn-secondary ml-2"
        :disabled="regionDetails.id === undefined"
        @click="deleteRegion"
        v-text="$i18n('region.delete')"
      />
      <button
        id="masterUpdateButton"
        v-b-tooltip.hover="$i18n('region.hull.closure', {region: regionDetails.name})"
        type="button"
        class="btn btn-secondary ml-2"
        :disabled="regionDetails.id === undefined"
        @click="startMasterUpdate"
        v-text="$i18n('region.hull.start')"
      />
    </div>

    <div class="row">
      <region-tree
        ref="regionTree"
        class="col-4 page-container region-tree"
        @change="onRegionSelected"
      />

      <region-form
        :region-details.sync="regionDetails"
        class="page-container col-8"
        @region-updated="(regionId, parentId) => updateNode(parentId, regionId)"
      />
    </div>
  </div>
</template>

<script>

import RegionForm from './RegionForm'
import RegionTree from '@/components/regiontree/RegionTree'
import { addRegion, getRegionDetails, masterUpdate } from '@/api/regions'
import { pulseError } from '@/script'
import { BOverlay } from 'bootstrap-vue'
import { deleteGroup } from '@/api/groups'

export default {
  components: { RegionForm, RegionTree, BOverlay },
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
     * Forces the region tree to refresh the child nodes of a region. Optionally selects a specified region after the
     * refresh.
     */
    async updateNode (regionId, selectRegion = null) {
      await this.$refs.regionTree.updateNode(regionId)
      if (selectRegion) {
        this.$refs.regionTree.selectRegion(selectRegion)
      }
    },
    runAfterConfirm (title, message, callback) {
      this.$bvModal.msgBoxConfirm(message, {
        modalClass: 'bootstrap',
        title: title,
        cancelTitle: this.$i18n('button.cancel'),
        okTitle: this.$i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      }).then(async accepted => {
        if (accepted) {
          this.isLoading = true
          await callback()
          this.isLoading = false
        }
      })
    },
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
      this.runAfterConfirm(
        this.$i18n('region.new'),
        this.$i18n('region.new_region_confirm', { parent: this.regionDetails.name }),
        async _ => {
          try {
            const child = await addRegion(this.regionDetails.id)

            // refresh the parent region's children to load the new region
            await this.updateNode(this.regionDetails.id, child.id)
          } catch (e) {
            pulseError(this.$i18n('error_unexpected'))
          }

          this.isLoading = false
        })
    },
    async deleteRegion () {
      this.runAfterConfirm(
        this.$i18n('region.delete'),
        this.$i18n('region.delete_confirm', { parent: this.regionDetails.name }),
        async _ => {
          try {
            await deleteGroup(this.regionDetails.id)

            // refresh the parent region's children to remove the deleted node
            await this.updateNode(this.regionDetails.parentId, this.regionDetails.parentId)
          } catch (e) {
            if (e.code === 409) {
              pulseError(this.$i18n('region.delete_conflict'))
            } else {
              pulseError(this.$i18n('error_unexpected'))
            }
          }
        },
      )
    },
    async startMasterUpdate () {
      this.runAfterConfirm(
        this.$i18n('region.hull.title'),
        this.$i18n('region.hull.confirm'),
        async _ => {
          try {
            await masterUpdate(this.regionDetails.id)
          } catch (e) {
            pulseError(this.$i18n('error_unexpected'))
          }
        },
      )
    },
  },
}
</script>

<style type="text/scss" scoped>
.region-tree {
  overflow: scroll;
  height: 400px;
  max-height: 400px;
}
</style>
