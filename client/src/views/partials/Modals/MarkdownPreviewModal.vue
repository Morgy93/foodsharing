<template>
  <b-modal
    id="markdownPreviewModal"
    ref="markdownPreviewModal"
    modal-class="testing-markdown-preview-Modal"
    :title="$i18n('globals.preview')"
    :cancel-title="$i18n('globals.close')"
    :ok-title="$i18n('globals.send')"
    size="xl"
    scrollable
  >
    <div class="markdown-preview-container">
      <b-form-textarea
        id="textarea"
        v-model="text"
        class="markdown-preview-editor"
        placeholder="Enter something..."
        rows="3"
        :max-rows="rowCount"
      />
      <div
        class="card border markdown-preview-content"
      >
        <div
          class="card-body"
          v-html="renderedText"
        />
      </div>
    </div>
  </b-modal>
</template>

<script>
// Others
import MarkdownIt from 'markdown-it'

const md = new MarkdownIt('commonmark')
export default {
  props: {
    input: {
      type: String,
      default: [
        '*Italic* or _Italic_',
        '',
        '**Bold** or __Bold__',
        '# Heading 1',
        'Heading 1',
        '=========',
        '## Heading 2',
        'Heading 2',
        '---------',
        '[Link](https://commonmark.org/) to CommonMark',
        '',
        '![Image](https://commonmark.org/help/images/favicon.png) CommonMark logo',
        '',
        '> Blockquote',
        '',
        '- List One [`-` or `*`]',
        '- List Two [`-` or `*`]',
        '- List Three [`-` or `*`]',
        '',
        '1. List One [`1.` or `1)`]',
        '1. List Two [`1.` or `1)`]',
        '1. List Three [`1.` or `1)`]',
        '',
        '---',
        '***',
        '`Inline code` with backticks',
        '```',
        '# code block',
        'print "3 backticks or"',
        'print "indent 4 spaces"',
        '``` ····# code block',
        '····print "3 backticks or"',
        '····print "indent 4 spaces"',
        '',
      ].join('\n'),
    },
  },
  data () {
    return {
      text: this.input,
    }
  },
  computed: {
    rowCount () {
      return this.text.split('\n').length
    },
    renderedText () {
      return md.render(this.text)
    },
  },
}
</script>

<style lang="scss" scoped>
.markdown-preview-container {
  display: flex;
}

.markdown-preview-editor{
  flex: 1;
}

.markdown-preview-content {
  margin-left: 1rem;
  flex: 1;
}
</style>
