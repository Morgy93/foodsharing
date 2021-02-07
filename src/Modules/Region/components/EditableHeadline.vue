<template>
  <div class="row m-1">
    <b-col
      v-if="editing"
    >
      <b-form-input
        v-model="editedText"
        @keydown.native="handleKey"
      />
    </b-col>
    <div
      v-else
      class="container"
    >
      <div class="row">
        <h4 class="text-truncate col">
          {{ text }}
        </h4>
        <div class="col-1">
          <b-button
            v-if="mayEdit"
            class="btn-sm"
            :title="$i18n('forum.thread.rename')"
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
    },
    handleKey (event) {
      // emit an event when the user presses enter in the text field
      if (event.which === 13) {
        this.editing = false
        if (this.editedText !== this.text) {
          this.$emit('text-changed', this.editedText)
        }
      }
    },
  },
}

</script>
