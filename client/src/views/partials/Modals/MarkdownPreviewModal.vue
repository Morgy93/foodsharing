<template>
  <b-modal
    id="markdownPreviewModal"
    ref="markdownPreviewModal"
    modal-class="testing-markdown-preview-Modal"
    :title="$i18n('markdown.title')"
    :cancel-title="$i18n('globals.close')"
    :ok-title="$i18n('globals.save')"
    size="xl"
    scrollable

    @shown="updateText"
    @ok="onOk"
  >
    <div class="markdown-container">
      <div
        class="markdown-toolbar btn-toolbar mb-3"
        role="toolbar"
      >
        <div class="btn-group">
          <button
            v-for="option in options"
            :key="option.style"
            v-b-tooltip="$i18n('markdown.'+ option.style)"
            class="btn btn-sm"
            :class="{
              'btn-light': !state
            }"
            :disabled="state"
            @click="insert(option.code, option.settings)"
          >
            <i
              class="fas"
              :class="option.icon"
            />
          </button>
        </div>
        <div class="btn-group ml-auto">
          <button
            class="btn btn-sm"
            :class="{
              'btn-primary': !state,
              'btn-light': state
            }"
            @click="setState(false)"
            v-html="$i18n('globals.edit')"
          />

          <button
            class="btn btn-sm"
            :class="{
              'btn-primary': state,
              'btn-light': !state
            }"
            @click="setState(true)"
            v-html="$i18n('globals.preview')"
          />
        </div>
      </div>
      <div
        v-if="!state"
        class="markdown-editor"
      >
        <b-form-textarea
          id="textarea"
          ref="textarea"
          v-model="text"
          :placeholder="$i18n('globals.placeholder.write_something')"
          rows="3"
          :max-rows="rowCount"
        />
      </div>
      <div
        v-else
        class="card border markdown-content"
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
import StateTogglerMixin from '@/mixins/StateTogglerMixin'

const md = new MarkdownIt('commonmark')
export default {
  mixins: [StateTogglerMixin],
  props: {
    input: {
      type: String,
      default: [
        '_Italic_',
        '',
        '**Bold**',
        '# Heading 1',
        '## Heading 2',
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
      options: [
        {
          style: 'bold',
          icon: 'fa-bold',
          code: (s) => `**${s}**`,
        },
        {
          style: 'italic',
          icon: 'fa-italic',
          code: (s) => `_${s}_`,
        },
        {
          style: 'heading-1',
          icon: 'fa-heading',
          code: (s) => `# ${s}`,
          settings: {
            forceFirstLine: true,
          },
        },
        {
          style: 'heading-2',
          icon: 'fa-heading',
          code: (s) => `## ${s}`,
          settings: {
            forceFirstLine: true,
          },
        },
        {
          style: 'quote',
          icon: 'fa-quote-right',
          code: (s) => `> ${s}`,
          settings: {
            forceFirstLine: true,
          },
        },
        {
          style: 'list-unordered',
          icon: 'fa-list-ul',
          code: (s) => `* ${s}`,
          settings: {
            forceFirstLine: true,
          },
        },
        {
          style: 'list-ordered',
          icon: 'fa-list-ol',
          code: (s) => `1. ${s}`,
          settings: {
            forceFirstLine: true,
          },
        },
        {
          style: 'code',
          icon: 'fa-code',
          code: (s) => `\`${s}\``,
        },
        {
          style: 'code-block',
          icon: 'fa-stream',
          code: (s) => '```\n' + s + '\n```',
        },
        {
          style: 'divider',
          icon: 'fa-grip-lines',
          code: (s) => s + '\n---\n',
          settings: {
            forceFirstLine: true,
          },
        },
        {
          style: 'link',
          icon: 'fa-link',
          code: (s) => `[Link Text](${s})`,
        },
      ],
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
  methods: {
    insert (style = (s) => `**${s}**`, { forceFirstLine = false } = {}) {
      const input = this.$refs.textarea
      if (input.selectionStart === input.selectionEnd && !forceFirstLine) {
        return
      }
      const end = input.selectionEnd
      const selected = input.value.slice(input.selectionStart, input.selectionEnd)
      const newTest = style(selected)
      input.setRangeText(style(selected))
      setTimeout(() => {
        input.selectionStart = input.selectionEnd = end + (newTest.length - selected.length)
      })
      input.focus()
    },
    updateText () {
      this.text = this.input
    },
    onOk () {
      this.$emit('input', this.text)
      this.setState(false)
    },
  },
}
</script>

<style lang="scss" scoped>
.markdown-toolbar {
  display: flex;
  @media (max-width: 990px) {
    flex-direction: column;
  }

  .btn-group {
    @media (max-width: 990px) {
      &:not(:last-child) {
        margin-bottom: 1rem;
      }
    }
  }
}

.markdown-editor {
  flex: 1;
}

.markdown-content {
  flex: 1;
}
</style>
