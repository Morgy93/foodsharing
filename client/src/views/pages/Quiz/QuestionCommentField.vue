<template>
  <div>
    <a
      ref="comment-collapse-toggle"
      v-b-toggle
      href="#comment-collapse"
      @click.prevent
    >
      {{ $i18n('quiz.comment.toggle') }}
    </a>
    <b-collapse
      id="comment-collapse"
      ref="comment-collapse"
      v-model="commentSectionVisible"
    >
      <b-form-textarea
        v-model="comment"
        label="Frage Kommentieren"
        :placeholder="$i18n('quiz.comment.placeholder')"
        rows="3"
      />
      <div class="send-button-wrapper">
        <b-button
          variant="primary"
          :disabled="!comment"
          @click="sendCommentHandler"
        >
          {{ $i18n('quiz.comment.send') }}
        </b-button>
      </div>
    </b-collapse>
  </div>
</template>

<script>

import { commentQuestion } from '@/api/quiz'
import { pulseSuccess } from '@/script'

export default {
  props: {
    questionId: {
      type: Number,
      required: true,
    },
  },
  data () {
    return {
      comment: '',
      commentSectionVisible: false,
    }
  },
  methods: {
    async sendCommentHandler () {
      await commentQuestion(this.questionId, this.comment)
      this.commentSectionVisible = false
      pulseSuccess(this.$i18n('quiz.comment.sent'))
    },
  },
}
</script>

<style scoped>
.send-button-wrapper {
  text-align: right;
  padding-top: .5em;
}
</style>
