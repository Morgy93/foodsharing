<template>
  <div class="bootstrap">
    <b-form-group v-if="smallLayout">
      <div
        v-for="i in options.length"
        :key="i"
        class="mb-5"
      >
        {{ options[i - 1].text }}
        <vue-slider
          v-model="selected[i-1]"
          :min="-3"
          :max="3"
          :value="0"
          :marks="marks"
          :adsorb="true"
          class="mt-2"
          :disabled="!enabled"
          direction="rtl"
        />
      </div>
    </b-form-group>
    <b-form-group v-else>
      <b-form-row
        v-for="i in options.length"
        :key="i"
        class="mb-5"
      >
        <b-col>
          {{ options[i - 1].text }}
        </b-col>
        <b-col>
          <vue-slider
            v-model="selected[i-1]"
            :min="-3"
            :max="3"
            :value="0"
            :marks="marks"
            :adsorb="true"
            :disabled="!enabled"
            direction="rtl"
          />
        </b-col>
      </b-form-row>
    </b-form-group>
  </div>
</template>

<script>

import { BFormGroup, BFormRow, BCol } from 'bootstrap-vue'
import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/antd.css'

export default {
  components: { BFormGroup, VueSlider, BFormRow, BCol },
  props: {
    options: {
      type: Array,
      required: true,
    },
    enabled: {
      type: Boolean,
      default: true,
    },
  },
  data () {
    return {
      selected: Array(this.options.length).fill(0),
      marks: [-3, -2, -1, 0, 1, 2, 3],
      smallLayout: window.innerWidth < 500,
    }
  },
  computed: {
    votingRequestValues: function () {
      const v = {}
      for (let i = 0; i < this.selected.length; i++) {
        v[this.options[i].optionIndex] = this.selected[i]
      }
      return v
    },
  },
  watch: {
    votingRequestValues () { this.$emit('update-voting-request-values', this.votingRequestValues) },
  },
  created () {
    this.$emit('update-valid-selection', true)
    this.$emit('update-voting-request-values', this.votingRequestValues)
    window.addEventListener('resize', this.updateWidth)
  },
  methods: {
    updateWidth (event) {
      this.smallLayout = window.innerWidth < 500
    },
  },
}
</script>

<style scoped lang="scss">
</style>
