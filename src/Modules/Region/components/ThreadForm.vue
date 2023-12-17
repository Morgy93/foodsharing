<template>
  <div class="bootstrap">
    <div class="card">
      <div class="card-header">
        {{ $i18n('button.answer') }}
      </div>
      <div class="card-body">
        <MarkdownInput
          ref="input"
          :rows="3"
          :value="text"
          :conceal-toolbar="true"
          @update:value="newValue => text = newValue"
          @submit="submit"
        />
      </div>
      <div class="card-footer below">
        <div class="row">
          <div class="col">
            <button
              :disabled="!text.trim()"
              class="btn btn-primary float-right"
              @click="submit"
            >
              {{ $i18n('button.send') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import MarkdownInput from '@/components/Markdown/MarkdownInput.vue'

export default {
  components: { MarkdownInput },
  data () {
    return {
      text: '',
    }
  },
  methods: {
    submit () {
      if (!this.text.trim()) {
        return
      }
      this.$emit('submit', this.text.trim())
      this.text = ''
    },
    focus () {
      this.$refs.input.setFocus(this.text.length)
    },
    prepend (text) {
      this.text = text + this.text
    },
  },
}
</script>
