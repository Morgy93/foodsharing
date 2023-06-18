<template>
  <div>
    <slot
      name="head"
      :showConfigurationDialog="showConfigurationDialog"
    >
      <h2>Use the named slot "head" to control this content.</h2>
      <button
        type="button"
        @click="$refs['configure-modal'].show()"
      >
        configre
      </button>
    </slot>
    <slot />
    <b-modal
      ref="configure-modal"
      title="Configure"
      modal-class="bootstrap"
      centered
      size="lg"
      hide-header-close
    >
      <b-form-group
        v-slot="{ ariaDescribedby }"
        label="Configure:"
      >
        <b-form-checkbox
          v-for="field in fields"
          :key="field.key"
          v-model="selectedFields"
          :value="field.key"
          :aria-describedby="ariaDescribedby"
        >
          <div>
            {{ field.label }}<button draggable="true">
              grab
            </button>
          </div>
        </b-form-checkbox>
      </b-form-group>
    </b-modal>
  </div>
</template>

<script>
export default {
  props: {
    fields: {
      type: Array,
      required: true,
    },
    value: {
      type: Array,
      required: true,
    },
  },
  data () {
    return {}
  },
  computed: {
    selectedFields: {
      get () {
        return this.value
      },
      set (value) {
        this.$emit('input', value)
      },
    },
  },
  methods: {
    showConfigurationDialog () {
      this.$refs['configure-modal'].show()
    },
  },
}
</script>
