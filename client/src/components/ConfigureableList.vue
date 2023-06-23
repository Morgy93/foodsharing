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
      initialData: [],
      defaultData: [],
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
      set (value) {
        console.log('fields got update', value)
        this.$emit('update:fields', value)
        if (this.store && this.dataLoaded) {
          this.unsavedChanges = true
        }
      },
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
  // mounted () {
  //   this.showConfigurationDialog()
  // },
  methods: {
    showConfigurationDialog () {
      this.$refs['configure-modal'].show()
    },
    save () {
      console.log('saving...')
      storage.set(`${this.storageKey}-fields`, this.fields.map(field => field.key))
      storage.set(`${this.storageKey}-selection`, this.selection)
      this.unsavedChanges = false
    },
    load () {
      console.log('loading...', storage)
      const fields = storage.get(`${this.storageKey}-fields`)
      if (fields) {
        this.componentFields = fields.map(field => { return { key: field } })
      }
      const selection = storage.get(`${this.storageKey}-selection`)
      if (selection) {
        this.componentSelection = selection
      }
      this.dataLoaded = true
    },
    reset () {
      console.log('reset')
    },
    unsavedChangesPrompt () {
      alert('You have unsaved changes')
    },
  },
}
</script>
