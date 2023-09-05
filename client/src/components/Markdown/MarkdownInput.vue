<template>
  <div>
    <b-button-toolbar>
      <b-button-group
        size="sm"
        class="md-button-toolbar"
      >
        <b-button
          v-b-tooltip.hover="$i18n('md_input.tooltip.bold')"
          @click="bold"
        >
          <i class="fas fa-bold" />
        </b-button>
        <b-button @click="italic">
          <i class="fas fa-italic" />
        </b-button>
        <b-button @click="strikethrough">
          <i class="fas fa-strikethrough" />
        </b-button>
        <b-button @click="heading">
          <i class="fas fa-heading" />
        </b-button>
        <b-button @click="link">
          <i class="fas fa-link" />
        </b-button>
        <b-button @click="code">
          <i class="fas fa-code" />
        </b-button>
        <b-button @click="quote">
          <i class="fas fa-quote-right" />
        </b-button>
        <b-button @click="unorderedList">
          <i class="fas fa-list" />
        </b-button>
        <b-button @click="orderedList">
          <i class="fas fa-list-ol" />
        </b-button>
        <b-button @click="hrule">
          <i class="fas fa-minus" />
        </b-button>
        <!-- <b-button><i class="fas fa-at" /></b-button> supposed to be a way to insert links to user profiles easily -->
        <!-- <b-button><i class="fas fa-table" /></b-button> -->
        <b-button @click="preview">
          <i class="fas fa-eye" />
        </b-button>
      </b-button-group>
    </b-button-toolbar>

    <b-form-textarea
      ref="input"
      v-model="value"
      class="md-text-area"
      :rows="rows"
      :state="state"
      @input="inputEvent"
    />
    <b-modal
      id="preview-modal"
      title="Formatierungsvorschau"
      centered
      ok-only
    >
      <Markdown :source="value" />
    </b-modal>
  </div>
</template>

<script>
import Markdown from './Markdown.vue'
export default {
  components: { Markdown },
  props: {
    rows: {
      type: Number,
      default: 4,
    },
    initialValue: {
      type: String,
      default: '',
    },
    state: {
      type: Boolean,
      default: null,
    },
  },
  data () {
    return {
      value: this.initialValue,
    }
  },
  computed: {
    baseTextArea () {
      return this.$refs.input?.$refs?.input
    },
  },
  methods: {
    bold () {
      this.wrapSelection('**')
    },
    italic () {
      this.wrapSelection('*')
    },
    strikethrough () {
      this.wrapSelection('~~')
    },
    heading () {
      const [start, end] = this.getSelection()
      const lineStart = this.getLineStart(start)
      let lineStarter = '#'
      const fromCurrentLine = this.value.substring(lineStart)
      if (!/^#* /.test(fromCurrentLine)) {
        lineStarter += ' '
      } else if (/^#{6} /.test(fromCurrentLine)) {
        lineStarter = ''
      }
      this.value = this.value.substring(0, lineStart) + lineStarter + this.value.substring(lineStart)
      this.setFocus(start + lineStarter.length, end + lineStarter.length)
    },
    link () {
      this.wrapSelection('[', '](https://)')
    },
    code () {
      const [start, end] = this.getSelection()
      const selected = this.value.substring(start, end)
      if (selected.includes('\n')) {
        const breakIf = (condition) => condition ? '\n' : ''
        const wrapStart = breakIf(selected.startsWith('\n')) + '```' + breakIf(this.value.substring(0, start).endsWith('\n'))
        const wrapEnd = breakIf(this.value.substring(end).startsWith('\n')) + '```' + breakIf(selected.endsWith('\n'))
        this.wrapSelection(wrapStart, wrapEnd)
      } else {
        this.wrapSelection('`')
      }
    },
    quote () {
      this.prefixLines('\n', '\n> ', true)
    },
    unorderedList () {
      this.prefixLines(/\n(?!- )/g, '\n- ')
    },
    orderedList () {
      this.prefixLines(/\n(?!\d\. )/g, '\n1. ')
    },
    hrule () {
      this.wrapSelection('\n\n---\n\n', '')
    },
    prefixLines (pattern, replacement, includeEmptyLine = false) {
      let [start, end] = this.getSelection()
      let selected = this.value.substring(start, end)
      start += Number(selected.startsWith('\n'))
      end -= Number(selected.endsWith('\n'))
      start = this.getLineStart(start)
      const restOfLineLength = this.value.substring(end).indexOf('\n')
      if (restOfLineLength !== -1) {
        end += restOfLineLength
      } else {
        end = this.value.length
      }
      selected = this.value.substring(start, end)
      selected = ('\n' + selected).replaceAll(pattern, replacement).substring(1)
      if (includeEmptyLine) {
        selected += (this.value.substring(end).startsWith('\n\n') ? '' : '\n')
      }
      this.value = this.value.substring(0, start) + selected + this.value.substring(end)
      end = start + selected.length
      this.setFocus(start, end)
    },
    getLineStart (position) {
      return this.value.substring(0, position).lastIndexOf('\n') + 1
    },
    wrapSelection (wrapper, secondWrapper = wrapper) {
      const [start, end] = this.getSelection()
      this.value = this.value.substring(0, start) + wrapper + this.value.substring(start, end) + secondWrapper + this.value.substring(end)
      this.setFocus(start + wrapper.length, end + wrapper.length)
    },
    getSelection () {
      return [this.baseTextArea.selectionStart, this.baseTextArea.selectionEnd]
    },
    async setFocus (start, end) {
      this.$refs.input.focus()
      await new Promise(resolve => window.requestAnimationFrame(resolve))
      this.baseTextArea.selectionEnd = end
      this.baseTextArea.selectionStart = start
    },
    preview () {
      this.$bvModal.show('preview-modal')
    },
    inputEvent () {
      this.$emit('update:value', this.value)
    },
  },
}
</script>

<style lang="scss" scoped>
.md-button-toolbar{
  flex-grow: 1;
  margin-right: 0;
  .btn {
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
  }
}

.md-text-area{
  border-top-left-radius: 0;
  border-top-right-radius: 0;
  border-top: 0;
}
</style>
