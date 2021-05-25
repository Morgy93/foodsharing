<!--
A stand-alone headline component whose text can be edited. A button allows switching to editing mode.
The 'contentBefore' slot is inserted before the headline text.
After editing it emits a 'text-changed' event with the new value.
-->
<template>
  <div class="row m-1">
    <b-col
      v-if="editing"
    >
      <b-form-input
        ref="inputField"
        v-model="editedText"
        @keydown.native="handleKey"
      />
    </b-col>
    <div
      v-else
      class="container"
    >
      <div class="row">
        <h4 class="text-truncate col p-0">
          <slot name="contentBefore" />
          {{ text }}
        </h4>
        <div class="col-1">
          <b-button
            v-if="mayEdit"
            class="btn-sm"
            :title="$i18n(editButtonTooltipKey)"
            @click="showInputField"
          >
            <i class="far fa-edit" />
          </b-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { BFormInput, BButton, BCol } from 'bootstrap-vue'

export default {
  components: { BFormInput, BButton, BCol },
  props: {
    text: { type: String, required: true },
    mayEdit: { type: Boolean, required: true },
    editButtonTooltipKey: { type: String, default: 'button.edit' },
  },
  data () {
    return {
      editing: false,
      editedText: this.text,
    }
  },
  methods: {
    showInputField () {
      this.editedText = this.text
      this.editing = true
      this.$nextTick(function () {
        this.$refs.inputField.focus()
      })
    },
    handleKey (event) {
      if (event.which === 13) {
        // emit an event when the user presses enter in the text field
        this.editing = false
        if (this.editedText !== this.text) {
          this.$emit('text-changed', this.editedText)
        }
      } else if (event.which === 27) {
        // switch back without emitting an event when the user presses escape
        this.editing = false
      }
    },
  },
}

</script>
