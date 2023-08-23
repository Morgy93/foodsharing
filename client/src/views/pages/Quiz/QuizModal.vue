<template>
  <b-modal
    :visible="visible"
    :title="$i18n('quiz.infomodal.title', { index: status?.answered+1, questions: status?.questions })"
    hide-header-close
    no-close-on-backdrop
    no-close-on-esc
    centered
    size="lg"
    scrollable
    @change="$emit('update:visible', $event.target.value)"
  >
    <b-form-group
      :label="question?.text"
    >
      <div
        v-for="answer in question?.answers"
        :key="answer.id"
        class="answer-wrapper"
        :class="isQuestionActive ? '' : answerColorClass(solutionById[answer.id]?.right, selectedAnswers[answer.id])"
      >
        <b-form-checkbox
          v-model="selectedAnswers[answer.id]"
          :disabled="!isQuestionActive"
        >
          {{ answer.text }}
        </b-form-checkbox>

        <div v-if="!isQuestionActive">
          <p>{{ $i18n(answerText(solutionById[answer.id]?.right, selectedAnswers[answer.id])) }}</p>
          <ExpandableExplanation
            :text="solutionById[answer.id]?.explanation"
          />
        </div>
      </div>
    </b-form-group>

    <QuestionCommentField
      v-if="!isQuestionActive"
      :question-id="question.id"
    />

    <template #modal-footer>
      <div
        v-if="question?.timed && isQuestionActive"
        class="time-bar"
      >
        <div
          ref="time-left"
          class="time-left"
        />
      </div>
      <b-button
        v-if="(!isQuestionActive || !status?.timed ) && !isQuizFinished"
        variant="outline-primary"
        @click="closeQuiz"
      >
        {{ $i18n('quiz.button.pause') }}
      </b-button>
      <b-button
        v-if="!isQuizFinished"
        variant="primary"
        @click="continueQuizHandler"
      >
        {{ $i18n('button.next') }}
      </b-button>
      <b-button
        v-if="isQuizFinished"
        variant="primary"
        @click="finishQuiz"
      >
        {{ $i18n('quiz.button.finish') }}
      </b-button>
    </template>
  </b-modal>
</template>

<script>

import { answerQuestion, getQuestion } from '@/api/quiz'
import { pulseError } from '@/script'
import QuestionCommentField from './QuestionCommentField'
import ExpandableExplanation from './ExpandableExplanation'

export default {
  components: {
    QuestionCommentField,
    ExpandableExplanation,
  },
  props: {
    quiz: {
      type: Object,
      required: true,
    },
    status: {
      type: Object,
      required: true,
    },
    visible: {
      type: Boolean,
      required: true,
    },
  },
  data () {
    return {
      isQuestionActive: true,
      selectedAnswers: {},
      question: null,
      questionStarted: null,
      solution: null,
    }
  },
  computed: {
    isQuizFinished () {
      return !this.isQuestionActive && this.status.answered + 1 >= this.status.questions
    },
    solutionById () {
      if (!this.solution) return {}
      return Object.fromEntries(this.solution.solution.map(a => [a.id, a]))
    },
  },
  methods: {
    async handInQuestion () {
      const selected = Object.entries(this.selectedAnswers).filter(a => a[1]).map(a => +a[0])
      this.solution = await answerQuestion(this.quiz.id, selected)
      this.isQuestionActive = false
      window.clearTimeout(this.timeOutTimer)
    },
    async showNextQuestion () {
      await Promise.all([
        new Promise(resolve => this.$emit('fetch-status', resolve)),
        this.fetchQuestion(),
      ])
      if (this.status.answered !== this.question.index) {
        pulseError('Die Zeit deiner letzten Frage ist bereits abgelaufen.')
        this.$emit('update:questionsAnswered', this.question.index)
      }
      this.$emit('update:visible', true)
      this.animateTimer()
      this.questionStarted = Date.now()
      this.isQuestionActive = true
      this.selectedAnswers = {}
    },
    async fetchQuestion () {
      if (this.isFetching) return
      this.isFetching = true
      this.question = (await getQuestion(this.quiz.id)).question
      this.isFetching = false
    },
    async animateTimer () {
      await new Promise(resolve => window.requestAnimationFrame(resolve))
      let duration = this.question.duration
      let initialWidth = 1
      if (this.question.age) {
        initialWidth -= this.question.age / this.question.duration
        duration -= this.question.age
      }
      if (this.$refs['time-left']) {
        this.$refs['time-left'].animate(
          [{ width: initialWidth * 100 + '%' }, { width: '0%' }],
          { duration: duration * 1000, iterations: 1 },
        )
        this.timeOutTimer = window.setTimeout(this.timeOut, duration * 1000)
      }
    },
    finishQuiz () {
      this.closeQuiz()
      this.$emit('finished-quiz')
    },
    closeQuiz () {
      this.$emit('update:visible', false)
      this.$emit('fetch-status')
    },
    timeOut () {
      pulseError('Die Zeit ist um.')
      this.handInQuestion()
    },
    continueQuizHandler () {
      if (this.isQuestionActive) {
        this.handInQuestion()
        return
      }
      this.showNextQuestion()
    },
    answerColorClass (right, selected) {
      if (right === 2) return 'neutral'
      if (!right ^ selected) return 'success'
      return 'failiure'
    },
    answerText (right, selected) {
      const path = 'quiz.answers.'
      if (right === 2) return path + 'neutral'
      return `${path}${!!selected}_${!!right}`
    },
  },
}
</script>

<style lang="scss" scoped>
.time-bar {
  flex-grow: 1;
  height: 1em;
  border: 1px solid var(--fs-color-primary-500);
  border-radius: 1em;
  overflow: hidden;
  text-align:center;
  position: relative;
  span {
    position: absolute;
    transform: translate(-50%, -4px);
  }
}
.time-left {
  height: 100%;
  background-color: var(--fs-color-primary-500);
  width: 0%;
}

.answer-wrapper {
  padding: .5em 2em;
  border-radius: 1em;
  margin-bottom: 1em;
  ::v-deep .custom-control-label {
    color: currentColor;
  }

  &:not(.neutral) ::v-deep a {
    color: currentColor;
  }
}

.success {
  background-color: var(--fs-color-success-500);
  color:white;
}
.failiure {
  background-color: var(--fs-color-danger-500);
  color:white;
}
.neutral {
  background-color: var(--fs-color-warning-200);
}

</style>
