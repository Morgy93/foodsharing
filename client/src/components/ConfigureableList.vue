<template>
  <div>
    <b-button
      @click="allFields = fields.concat().reverse()"
    >
      Reverse
    </b-button>
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
      @shown="onModalHasOpened"
    >
      <b-form-group
        ref="drag-drop-list"
        v-slot="{ ariaDescribedby }"
        label="Configure:"
      >
        <DragAndDropSortList :value="fields">
          <template #item="item">
            {{ item }}
          </template>
        </DragAndDropSortList>
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
import { ref, toRef } from 'vue'
import { useDragAndDropSortableList } from '@/composeables/DragAndDropSortList'
import DragAndDropSortList from '@/components/DragAndDropSortList.vue'

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
  },
  setup (props) {
    const fields = toRef(props, 'fields')
    const dragDropList = ref(null)

    const { setupDragAndDropList } = useDragAndDropSortableList(dragDropList, fields)

    return { dragDropList, setupDragAndDropList }
  },
  data () {
    return {}
  },
  computed: {
    selectedFields: {
      get () {
        return this.selection
      },
      set (value) {
        this.$emit('update:selection', value)
      },
    },
    allFields: {
      get () {
        return this.fields
      },
      set (value) {
        this.$emit('update:fields', value)
      },
    },
  },
  methods: {
    showConfigurationDialog () {
      this.$refs['configure-modal'].show()
    },
    onModalHasOpened () {
      this.dragDropList = this.$refs['drag-drop-list']
      this.setupDragAndDropList()
    },
    emitData (data) {
      this.$emit('input', data)
    },
  },
}
</script>
