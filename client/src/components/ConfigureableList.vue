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
    >
      <b-form-group
        ref="drag-drop-list"
        v-slot="{ ariaDescribedby }"
        label="Configure:"
      >
        <DragAndDropSortList v-model="allFields">
          <template #item="{ item, onDragStart }">
            <b-form-checkbox
              v-model="selectedFields"
              :value="item.key"
              :aria-describedby="ariaDescribedby"
            >
              <div>
                {{ item.label }}
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
      </b-form-group>
    </b-modal>
  </div>
</template>

<script>
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
  },
}
</script>
