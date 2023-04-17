<!-- Wrapper class that allows using the RegionTree component in v-forms. -->
<template>
  <div
    id="input-wrapper"
    class="bootstrap input-wrapper"
  >
    <label class="wrapper-label ui-widget">{{ title }}</label>
    <div>
      {{ selectedRegion.name }}
      <b-link
        class="btn btn-sm btn-secondary ml-2"
        @click="$refs.regionTreeModal.show()"
      >
        {{ $i18n('region.change') }}
      </b-link>
    </div>

    <b-modal
      ref="regionTreeModal"
      :title="$i18n('terminology.homeRegion')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.send')"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      @ok="onModalClosed"
    >
      <region-tree
        :selectable-region-types="selectableRegionTypes"
        @change="onRegionSelected"
      />
    </b-modal>

    <div class="element-wrapper">
      <input
        :name="inputName"
        :value="selectedRegion.id"
        type="hidden"
      >
    </div>
  </div>
</template>

<script>
import RegionTree from './RegionTree'
import { BLink, BModal } from 'bootstrap-vue'

export default {
  components: { BLink, BModal, RegionTree },
  props: {
    title: {
      type: String,
      required: true,
    },
    inputName: {
      type: String,
      required: true,
    },
    initialValue: {
      type: Object,
      default: function () {
        return {
          id: 0,
          name: 'Root',
        }
      },
    },
    // if not null, only these types of regions can be selected
    selectableRegionTypes: { type: Array, default: null },
  },
  data () {
    return {
      tmpSelectedRegion: null,
      selectedRegion: this.initialValue,
    }
  },
  methods: {
    onRegionSelected (region) {
      this.tmpSelectedRegion = region
    },
    onModalClosed (e) {
      this.selectedRegion = this.tmpSelectedRegion
    },
  },
}
</script>

<style lang="scss">

</style>
