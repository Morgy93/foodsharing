<template>
  <div>
    <slot
      name="head"
      :showConfigurationDialog="showConfigurationDialog"
    >
      <h4>Use the named slot "head" to control this content.</h4>
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
        <h4>{{ $i18n('configure_columns') }}</h4>
        <button type="button" @click="close" class="btn btn-sm no-shadow">
         <i class="fas fa-xmark" />
        </button>
      </template>
      <template #default>
        <DragAndDropSortList v-model="componentFields" class="mb-1">
          <template #item="{ item, onDragStart }">
            <div class="d-flex align-items-center checkbox-nest">
              <input
                :id="`${storageKey}-${item[fieldKey]}`"
                type="checkbox"
                :value="item[fieldKey]"
                v-model="componentSelection"
              />
            </div>
            <div class="d-flex align-items-center">
              <label :for="`${storageKey}-${item[fieldKey]}`" class="mb-0 ml-1">
                {{ item[fieldLabel] }}
              </label>
            </div>
            <button type="button" @dragstart="onDragStart" class="btn btn-sm ml-auto shadow-none" draggable="true">
              <i class="fas fa-bars" />
            </button>
          </template>
        </DragAndDropSortList>
        <hr/>
        <div v-if="filter" class="form-group form-check mb-0">
          <input type="checkbox" class="form-check-input" id="save-filter-checkbox" v-model="saveFilter">
          <label class="form-check-label" for="save-filter-checkbox" v-b-tooltip.hover="$i18n('save_filter_description')">{{ $i18n('save_filter_and_sort') }}</label>
        </div>
      </template>
      <template #modal-footer="{ ok }">
        <b-button @click="resetDefaults" class="col">{{ $i18n('button.reset_default') }}</b-button>
        <b-button variant="primary" @click="ok">{{ $i18n('button.save') }}</b-button>
      </template>
    </b-modal>
  </div>
</template>

<script>
import DragAndDropSortList from '@/components/DragAndDropSortList.vue'
import Storage from '@/storage'
import { arrayContentEquals, arrayEquals } from '@/utils'

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
    defaultSelection: {
      type: Array,
      default: function () { return this.selection },
    },
    defaultFields: {
      type: Array,
      default: function () { return this.fields.map(field => field[this.fieldKey]) },
    },
    filter: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      saveFilter: Boolean(this.filter),
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
        this.$emit('update:selection', value)
      },
    },
    componentFields: {
      get () {
        return this.fields
      },
      set (fieldsOrFieldKeys) {
        const value = typeof fieldsOrFieldKeys[0] === 'string' ? fieldsOrFieldKeys : fieldsOrFieldKeys.map(field => field[this.fieldKey])
        this.$emit('update:fields', value)
      },
    },
    componentFilter: {
      get () {
        return this.filter
      },
      set (value) {
        this.$emit('update:filter', value)
      },
    },
    fieldsOrder () {
      return this.fields.map(field => field[this.fieldKey])
    },
    unsavedChanges () {
      return !arrayEquals(this.initialFields, this.fieldsOrder) || !arrayContentEquals(this.initialSelection, this.componentSelection)
    }
  },
  created () {
    this.storage = new Storage(`vue-${this.storageKey}`)
    this.setInitialData()
    if (this.store) {
      this.load()
      window.addEventListener('beforeunload', this.unsavedChangesPrompt)
    }
    if (this.saveFilter) {
      this.componentFilter = this.storage.get('filter')
    }
  },
  watch: {
    saveFilter: () => {
      //del or set filter
    }
  },
  mounted () {
    // this.showConfigurationDialog()
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
      this.storage.set('fields', this.fieldsOrder)
      this.storage.set('selection', this.selection)
      this.setInitialData()
    },
    saveFilterAndSortOrder () {
      this.storage.set('filter')
    },
    load () {
      this.storage.getKeys().forEach(key => {
        const propName = key[0].toUpperCase() + key.slice(1)
        const data = this.storage.get(key)
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
      console.log('resetting')
      this.componentSelection = this.initialSelection
      this.componentFields = this.initialFields
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
  $border: solid 0.6px #CED4DA;
  @mixin has-border {
    border: $border;
    border-radius: 2px;
  }

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

    ::v-deep >  div {
      display: flex;
      justify-content: flex-start;
      align-items: stretch;
      @include has-border;

      input, label {
        position: initial;
      }
    }
  }
  .has-border {
    @include has-border
  }
  .checkbox-nest {
    padding: 8px;
    background: var(--fs-color-gray-200);
  }
</style>
