<template>
  <div class="bootstrap">
    <div class="card">
      <div class="card-header">
        {{ $i18n('button.answer') }}
      </div>
      <div class="card-body">
        <p v-html="$i18n('forum.markdown_description')" />
        <textarea
          ref="textarea"
          v-model="text"
          class="form-control"
          :placeholder="$i18n('globals.placeholder.write_something')"
          rows="3"
          @keyup.ctrl.enter="submit"
        />
      </div>
      <div class="card-footer below">
        <button
          class="btn btn-sm btn-light"
          @click="$bvModal.show('markdownPreviewModal')"
        >
          {{ $i18n('markdown.title') }}
        </button>
        <button
          :disabled="!text.trim()"
          class="btn btn-primary"
          @click="submit"
        >
          {{ $i18n('button.send') }}
        </button>
      </div>
    </div>
    <markdownPreviewModal
      :input.sync="text"
      @input="updateText"
    />
  </div>
</template>

<script>
import markdownPreviewModal from '@/views/partials/Modals/MarkdownPreviewModal.vue'

export default {
  components: {
    markdownPreviewModal,
  },
  props: {},
  data () {
    return {
      text: '',
    }
  },
  methods: {
    submit () {
      if (!this.text.trim()) return
      this.$emit('submit', this.text.trim())
      this.text = ''
    },
    updateText (text) {
      this.text = text
    },
    focus () {
      this.$refs.textarea.focus()
    },
  },
}
</script>

<style lang="scss" scoped>
.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
