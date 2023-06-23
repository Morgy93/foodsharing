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
import { debounce } from '@/utils'

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
    }
  },
  computed: {
    componentSelection: {
      get () {
        return this.selection
      },
      set (value) {
        console.log('selection got update')
        this.$emit('update:selection', value)
        if (this.store) {
          this.debouncedSave()
        }
      },
    },
    componentFields: {
      get () {
        return this.fields
      },
      set (value) {
        console.log('fields got update')
        this.$emit('update:fields', value)
        if (this.store) {
          this.debouncedSave()
        }
      },
    },
  },
  created () {
    console.log(this.store, this.storageKey)
    console.log('parent name:', this.$parent.$options._componentTag)
    if (this.store) {
      // wait 2 seconds before saving settings
      this.debouncedSave = debounce(() => {
        this.unsavedChanges = true
        this.save()
      }, 2000)
      this.load()
      window.addEventListener('beforeunload', this.imidiateSave)
    }
  },
  destroyed () {
    if (this.store) {
      window.removeEventListener('beforeunload', this.imidiateSave)
    }
  },
  mounted () {
    this.showConfigurationDialog()
  },
  methods: {
    showConfigurationDialog () {
      this.$refs['configure-modal'].show()
    },
    save () {
      console.log('saving...')
      storage.set(`${this.storageKey}-fields`, this.fields)
      storage.set(`${this.storageKey}-selection`, this.selection)
      this.unsavedChanges = false
    },
    imidiateSave () {
      if (this.unsavedChanges) {
        this.save()
      }
    },
    load () {
      console.log('loading...', storage)
      storage.getKeys().forEach(key => {
        const storaKeyLength = this.storageKey.length + 1
        let propName = key.substring(storaKeyLength)
        propName = propName[0].toUpperCase() + propName.slice(1)
        propName = 'component' + propName
        console.log('loaded prop:', propName)
        const data = storage.get(key)
        if (data) {
          this[propName] = data
        }
      })
    },
  },
}
</script>
