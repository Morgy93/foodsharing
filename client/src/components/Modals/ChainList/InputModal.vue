<template>
  <b-modal
    ref="input-modal"
    :title="$i18n('chain.inputmodal.title.' + mode)"
    modal-class="bootstrap"
    centered
    size="lg"
    hide-header-close
    no-close-on-backdrop
    no-close-on-esc
    @ok="finishedEditing"
  >
    <form>
      <b-form-group
        :label="$i18n('chain.inputmodal.inputs.name.label')"
        label-for="name-input"
        label-cols-sm="2"
        label-align-sm="right"
        :invalid-feedback="$i18n('chain.inputmodal.inputs.name.invalidfeedback')"
      >
        <b-form-input
          id="name-input"
          v-model="input.name"
          :placeholder="$i18n('chain.inputmodal.inputs.name.placeholder')"
          :formatter="singleSpacing"
          lazy-formatter
          :state="!!input.name"
          maxlength="120"
          trim
        />
      </b-form-group>

      <b-form-group
        id="headquarters-input"
        :label="$i18n('chain.inputmodal.inputs.headquarters.label')"
        label-for="zip-input"
        label-cols-sm="2"
        label-align-sm="right"
      >
        <b-form-input
          id="zip-input"
          v-model="input.headquarters_zip"
          :placeholder="$i18n('chain.inputmodal.inputs.headquarters.placeholder.zip')"
          :state="input.headquarters_zip ? /^\d{5}$/.test(input.headquarters_zip) : null"
          maxlength="5"
          trim
        />
        <b-form-input
          id="city-input"
          v-model="input.headquarters_city"
          :placeholder="$i18n('chain.inputmodal.inputs.headquarters.placeholder.city')"
          :formatter="singleSpacing"
          :state="input.headquarters_city ? true : null"
          maxlength="50"
          trim
        />
      </b-form-group>

      <b-form-group
        :label="$i18n('chain.inputmodal.inputs.status.label')"
        label-for="status-input"
        label-cols-sm="2"
        label-align-sm="right"
      >
        <b-form-select
          id="status-input"
          v-model="input.status"
          :options="statusFilterOptions.slice(1)"
        />
      </b-form-group>

      <b-form-group
        :label="$i18n('chain.inputmodal.inputs.thread.label')"
        label-for="thread-input"
        label-cols-sm="2"
        label-align-sm="right"
        :invalid-feedback="$i18n('chain.inputmodal.inputs.thread.invalidfeedback')"
        :description="$i18n('chain.inputmodal.inputs.thread.description')"
      >
        <b-form-input
          id="thread-input"
          v-model="input.forum_thread"
          placeholder="foodsharing.de/ ... sub=forum&tid=12345"
          :formatter="x => /tid=(\d+)/.test(input.forum_thread) ? x.match(/tid=(\d+)/)[1] : x"
          :state="input.forum_thread ? /(tid=(\d+))|^\d+$/.test(input.forum_thread) : null"
          lazy-formatter
          trim
        />
      </b-form-group>

      <b-form-group
        :label="$i18n('chain.inputmodal.inputs.kam.label')"
        label-for="kams-input"
        label-cols-sm="2"
        label-align-sm="right"
        :invalid-feedback="$i18n('chain.inputmodal.inputs.kam.invalidfeedback')"
        :description="$i18n('chain.inputmodal.inputs.kam.description')"
      >
        <b-form-input
          id="kams-input"
          v-model="input.kamIds"
          placeholder="12345, 67890, ..."
          :state="input.kamIds ? /^(\d+\s*,\s*)*\d+\s*,?$/.test(input.kamIds) : null"
          :formatter="formatKams"
          lazy-formatter
          trim
          :disabled="!adminPermissions"
        />
      </b-form-group>

      <b-form-group
        :label="$i18n('chain.inputmodal.inputs.press.label')"
        label-for="press-input"
        label-cols-sm="2"
        label-align-sm="right"
      >
        <b-form-checkbox
          id="press-input"
          v-model="input.allow_press"
          size="lg"
        >
          {{ $i18n('chain.inputmodal.inputs.press.description') }}
        </b-form-checkbox>
      </b-form-group>

      <b-form-group
        :label="$i18n('chain.inputmodal.inputs.notes.label')"
        label-for="notes-input"
        label-cols-sm="2"
        label-align-sm="right"
        :invalid-feedback="$i18n('chain.inputmodal.inputs.notes.invalidfeedback')"
        :description="$i18n('chain.inputmodal.inputs.notes.description')"
      >
        <b-form-textarea
          id="notes-input"
          v-model="input.notes"
          rows="1"
          max-rows="3"
          :state="input.notes ? input.notes.length <= 200 : null"
          :formatter="singleSpacing"
          lazy-formatter
        />
      </b-form-group>

      <b-form-group
        :label="$i18n('chain.inputmodal.inputs.details.label')"
        label-for="details-input"
        label-cols-sm="2"
        label-align-sm="right"
        :description="$i18n('chain.inputmodal.inputs.details.description')"
      >
        <b-form-textarea
          id="details-input"
          v-model="input.common_store_information"
          rows="1"
        />
      </b-form-group>
    </form>

    <template #modal-footer="{ ok, cancel }">
      <b-button
        variant="outline-danger"
        @click="cancel()"
      >
        {{ $i18n('chain.inputmodal.cancel') }}
      </b-button>
      <b-button
        variant="primary"
        @click="ok()"
      >
        {{ $i18n('chain.inputmodal.ok') }}
      </b-button>
    </template>
  </b-modal>
</template>

<script>
export default {
  props: {
    statusFilterOptions: {
      type: Array,
      required: true,
    },
    adminPermissions: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      input: {},
      chainEditing: -1, // The id of the chain to be edited or -1 if a new chain should be created instead
    }
  },
  computed: {
    mode () {
      return (this.chainEditing >= 0 ? 'edit' : 'new')
    },
  },
  methods: {
    show (chainEditing, inputData) {
      this.chainEditing = chainEditing
      this.input = inputData
      this.$refs['input-modal'].show()
    },
    singleSpacing (str) {
      return str.trim().replaceAll(/\s+/g, ' ')
    },
    formatKams (str) {
      let ids = str.matchAll(/\d+/g)
      ids = [...ids].map(x => x[0])
      ids = [...new Set(ids)]
      return ids.join(', ')
    },
    finishedEditing () {
      const data = {
        name: this.input.name,
        headquarters_zip: this.input.headquarters_zip,
        headquarters_city: this.input.headquarters_city,
        status: this.input.status,
        forum_thread: this.input.forum_thread || null,
        allow_press: this.input.allow_press,
        notes: this.input.notes,
        common_store_information: this.input.common_store_information,
        kams: this.input.kamIds ? this.input.kamIds.split(',').map(id => +id) : [],
      }
      this.$emit('ok', this.chainEditing, data)
    },
  },
}
</script>

<style lang="scss" scoped>

#headquarters-input::v-deep > div {
  display: flex;

  #zip-input {
    width: 100px;
    margin-right: 1em;
  }
}

::v-deep .custom-checkbox.b-custom-control-lg .custom-control-label {
  top: .4rem;

  &::after,
  &::before {
    top: -2px;
  }
}
</style>
