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
        configure
      </button>
    </slot>
    <slot />
    <b-modal
      ref="configure-modal"
      modal-class="bootstrap"
      floating
      size="lg"
      hide-header-close
      @ok="save"
      @close="reset"
      @hide="handleCloseOnEscOrBackdrop"
    >
      <template #modal-header="{ close }">
        <h2>{{ $i18n('configure_columns') }}</h2>
        <button type="button" @click="close" class="btn btn-sm">
         <i class="fas fa-xmark" />
        </button>
      </template>
      <template #default>

      <b-form-group
        v-slot="{ ariaDescribedby }"
        aria-describedby=""
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
                </div>
              </b-form-checkbox>
              <button type="button" @dragstart="onDragStart" class="btn btn-sm" draggable="true">
                <i class="fas fa-bars" />
              </button>
            </template>
          </DragAndDropSortList>
        </b-form-checkbox-group>
      </b-form-group>
      </template>
      <template #modal-footer="{ ok }">
        <b-button @click="resetDefaults">{{ $i18n('button.reset_default') }}</b-button>
        <b-button variant="primary" @click="ok">{{ $i18n('button.save') }}</b-button>
      </template>
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
      initialFields: [], // todo: fix is empty array
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
      window.addEventListener('beforeunload', this.unsavedChangesPrompt)
    }
  },
  mounted () {
    this.showConfigurationDialog()
  },
  destroyed () {
    if (this.store) {
      window.removeEventListener('beforeunload', this.unsavedChangesPrompt)
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
      storage.getKeys(this.storageKey).forEach(key => {
        const storaKeyLength = this.storageKey.length + 1
        let propName = key.substring(storaKeyLength)
        propName = propName[0].toUpperCase() + propName.slice(1)
        const data = storage.get(key)
        if (data) {
          console.log(key, data)
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
      console.log('resetting')
      this.componentSelection = this.initialSelection
      this.componentFields = this.initialFields
      this.unsavedChanges = false
    },
    resetDefaults () {
      this.componentSelection = this.defaultSelection
      this.componentFields = this.defaultFields
    },
    handleCloseOnEscOrBackdrop (event) {
      // https://github.com/bootstrap-vue/bootstrap-vue/issues/3164
      if (event.trigger === 'esc' || event.trigger === 'backdrop') {
        this.reset()
      }
    },
    unsavedChangesPrompt (event) {
      if (this.unsavedChanges) {
        // https://developer.mozilla.org/en-US/docs/Web/API/Window/beforeunload_event
        event.preventDefault()
        return (event.returnValue = "")
      }
    },
  },
}
</script>

<style scoped lang="scss">
  .btn.btn-secondary {
    border-color: var(--theme-dark, #4B4F58);
  }

  .modal-body .drag-drop-container {
    margin: -1rem; //counter .modal-body padding
    display: flex;
    align-items: stretch;
    flex-flow: column nowrap;
    gap: 16px;
    padding: 8px 16px;
    outline: lime 2px dotted;

    ::v-deep >  div {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      background: pink;

      input, label {
        position: initial;
      }
    }
  }
  span {
    border: 0.6px solid var(--components-form-border-default, #CED4DA);
    background: var(--components-form-background-default, #FFF);

    border-radius: 2px;
  }
</style>
<style lang="scss">

</style>
