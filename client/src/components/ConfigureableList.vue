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
      @ok="save"
      @cancel="reset"
    >
      <b-form-group
        v-slot="{ ariaDescribedby }"
        label="Configure:"
        aria-describedby="something"
      >
        <b-form-checkbox-group
          v-model="componentSelection"
          :aria-describedby="ariaDescribedby"
        >
          <DragAndDropSortList v-model="componentFields">
            <template #item="{ item, onDragStart }">
              <b-form-checkbox
                :value="item[fieldKey]"
                :aria-describedby="ariaDescribedby"
              >
                <div>
                  {{ item[fieldLabel] }}
                  <button
                    draggable="true"
                    @dragstart="onDragStart()"
                  >
                    grab
                  </button>
                </div>
              </b-form-checkbox>
            </template>
          </DragAndDropSortList>
        </b-form-checkbox-group>
      </b-form-group>
    </b-modal>
  </div>
</template>

<script>
import DragAndDropSortList from '@/components/DragAndDropSortList.vue'
import Storage from '@/storage'

const storage = new Storage('vue')
export default {
  components: { DragAndDropSortList },
  props: {
    fields: {
      type: Array,
      required: true,
    },
    selection: {
      type: Array,
      required: true,
    },
    defaultSelection: {
      type: Array,
      default: function () { return this.selection },
    },
    defaultFields: {
      type: Array,
      default: function () { return this.fieldsOrder },
    },
    fieldKey: {
      type: String,
      default: 'key',
    },
    fieldLabel: {
      type: String,
      default: 'label',
    },
    storageKey: {
      type: String,
      default: function () { return this.$parent.$options._componentTag },
    },
    store: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      unsavedChanges: false,
      dataLoaded: false,
      initialSelection: [],
      initialFields: [],
    }
  },
  computed: {
    componentSelection: {
      get () {
        return this.selection
      },
      set (value) {
        console.log('selection got update', value)
        this.$emit('update:selection', value)
        if (this.store && this.dataLoaded) {
          this.unsavedChanges = true
        }
      },
    },
    componentFields: {
      get () {
        return this.fields
      },
      set (fieldsOrFieldKeys) {
        const value = typeof fieldsOrFieldKeys[0] === 'string' ? fieldsOrFieldKeys : fieldsOrFieldKeys.map(field => field[this.fieldKey])
        console.log('fields got update', value)
        this.$emit('update:fields', value)
        if (this.store && this.dataLoaded) {
          this.unsavedChanges = true
        }
      },
    },
    fieldsOrder () {
      return this.fields.map(field => field[this.fieldKey])
    },
  },
  created () {
    console.log(this.store, this.storageKey)
    console.log('parent name:', this.$parent.$options._componentTag)
    if (this.store) {
      this.load()
      // window.addEventListener('beforeunload', this.save)
    }
  },
  destroyed () {
    if (this.store) {
      // window.removeEventListener('beforeunload', this.save)
    }
  },
  methods: {
    showConfigurationDialog () {
      this.$refs['configure-modal'].show()
    },
    save () {
      console.log('saving...')
      storage.set(`${this.storageKey}-fields`, this.fieldsOrder)
      storage.set(`${this.storageKey}-selection`, this.selection)
      this.unsavedChanges = false
      this.setInitialData()
    },
    load () {
      console.log('loading...', storage)
      storage.getKeys().forEach(key => {
        const storaKeyLength = this.storageKey.length + 1
        let propName = key.substring(storaKeyLength)
        propName = propName[0].toUpperCase() + propName.slice(1)
        const data = storage.get(key)
        if (data) {
          this['initial' + propName] = data
          this['component' + propName] = data
        }
      })
      this.dataLoaded = true
    },
    setInitialData () {
      this.initialSelection = this.selection
      this.initialFields = this.fieldsOrder
    },
    reset () {
      this.componentSelection = this.initialSelection
      this.componentFields = this.initialFields
    },
    resetDefaults () {
      this.componentSelection = this.defaultSelection
      this.componentFields = this.defaultFields
    },
    unsavedChangesPrompt () {
      alert('You have unsaved changes')
    },
  },
}
</script>
