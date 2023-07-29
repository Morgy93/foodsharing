<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <Container
    :title="title"
    :collapsible="false"
    :wrap-content="true"
  >
    <div v-html="quiz?.desc" />
    <div v-if="!isQuizModalShown">
      <div v-if="canStart">
        <b-button
          block
          variant="primary"
          @click="showStartModal(true)"
        >
          {{ $i18n('quiz.timedstart', {count: status?.questions}) }}
        </b-button>
        <b-button
          v-if="status?.allowUntimed"
          block
          variant="primary"
          @click="showStartModal(false)"
        >
          {{ $i18n('quiz.regstart', {count: status?.questions * 2}) }}
        </b-button>
      </div>

      <div v-if="canContinue">
        <b-button
          block
          variant="primary"
          @click="showStartModal"
        >
          {{ $i18n('quiz.continuenow') }}
        </b-button>
      </div>
    </div>

    <b-modal
      ref="start-info-modal"
      :title="$i18n('quiz.startmodal.title')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.start')"
      centered
      size="lg"
      scrollable
      @ok="initQuiz"
    >
      <ul>
        <li
          v-for="infoKey in infoKeys"
          :key="infoKey"
        >
          {{ $i18n(`quiz.startmodal.infos.${infoKey}`) }}
        </li>
      </ul>
    </b-modal>

    <b-modal
      v-model="isQuizModalShown"
      :title="$i18n('quiz.infomodal.title', { index: status.answered+1, questions: status.questions })"
      hide-header-close
      no-close-on-backdrop
      no-close-on-esc
      centered
      size="lg"
      scrollable
    >
      <b-form-group
        :label="question?.text"
      >
        <div
          v-for="answer in question?.answers"
          :key="answer.id"
          class="answer-wrapper"
          :class="isQuestionActive ? '' : answerColorClass(selectedAnswers[answer.id], solutionById[answer.id]?.right)"
        >
          <b-form-checkbox
            v-model="selectedAnswers[answer.id]"
            :disabled="!isQuestionActive"
          >
            {{ answer.text }}
          </b-form-checkbox>

          <div v-if="!isQuestionActive">
            <p> {{ $i18n(answerText(selectedAnswers[answer.id], solutionById[answer.id]?.right)) }}</p>
            <p>
              <span class="explanation">
                <b>Erklärung:</b>
                {{ solutionById[answer.id]?.explanation }}
              </span>
              <a class="show-full-expl" href="#">Mehr anzeigen</a>
            </p>
          </div>
        </div>
      </b-form-group>

      <div v-if="!isQuestionActive">
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
          <b-button
            variant="primary"
            :disabled="!comment"
            @click="sendCommentHandler"
          >
            {{ $i18n('quiz.comment.send') }}
          </b-button>
        </b-collapse>
      </div>

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
          v-if="(!isQuestionActive || !status.timed ) && !isQuizFinished"
          variant="outline-primary"
          @click="pauseQuiz"
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
  </Container>
</template>

<script>

import Container from '@/components/Container/Container.vue'
import i18n from '@/helper/i18n'
import { getQuestion, getQuizStatus, startQuiz, answerQuestion, commentQuestion } from '@/api/quiz'
import { pulseSuccess } from '@/script'

export default {
  components: {
    Container,
  },
  props: {
    quiz: {
      type: Object,
      default: () => null,
    },
  },
  data () {
    return {
      infoKeys: ['wiki', 'real_life_examples', 'limited_tries', 'alone', 'read_carefully', 'multiple_choice', 'comment', 'pause', 'feedback'],
      timed: undefined,
      isFetching: false,
      status: { status: undefined },
      question: null,
      selectedAnswers: {},
      questionStarted: null,
      solution: null,
      isQuestionActive: true,
      comment: '',
      commentSectionVisible: false,
      isQuizModalShown: false,
    }
  },
  computed: {
    title () {
      return i18n(`quiz.title.${this.status?.status}`, this.quiz)
    },
    canStart () {
      return [0, 3, 5].includes(this.status?.status)
    },
    canContinue () {
      return this.status?.status === 1
    },
    solutionById () {
      if (!this.solution) return {}
      return Object.fromEntries(this.solution.solution.map(a => [a.id, a]))
    },
    isQuizFinished () {
      return !this.isQuestionActive && this.status.answered + 1 >= this.status.questions
    },
  },
  mounted: function () {
    this.fetchStatus()
  },
  methods: {
    async fetchStatus () {
      this.status = await getQuizStatus(this.quiz.id)
    },
    async initQuiz () {
      if (!this.canContinue) {
        await startQuiz(this.quiz.id, this.timed)
      }
      this.showNextQuestion()
    },
    async fetchQuestion () {
      if (this.isFetching) return
      this.isFetching = true
      this.question = (await getQuestion(this.quiz.id)).question
      this.isFetching = false
    },
    async handInQuestion () {
      const selected = Object.entries(this.selectedAnswers).filter(a => a[1]).map(a => +a[0])
      this.solution = await answerQuestion(this.quiz.id, selected)
      this.isQuestionActive = false
    },
    async animateTimer () {
      await new Promise(resolve => window.requestAnimationFrame(resolve))
      this.$refs['time-left']?.animate(
        [{ width: '100%' }, { width: '0%' }],
        { duration: this.question.duration * 1000, iterations: 1 },
      )
    },
    showStartModal (timed) {
      this.timed = timed
      this.$refs['start-info-modal'].show()
    },
    async showNextQuestion () {
      await Promise.all([
        this.fetchStatus(),
        this.fetchQuestion(),
      ])
      this.isQuizModalShown = true
      this.animateTimer()
      this.questionStarted = Date.now()
      this.isQuestionActive = true
      this.selectedAnswers = {}
    },
    answerColorClass (selected, right) {
      if (right === 2) return 'neutral'
      if (!right ^ selected) return 'success'
      return 'failiure'
    },
    answerText (selected, right) {
      const path = 'quiz.answers.'
      if (right === 2) return path + 'neutral'
      return `${path}${!!selected}_${!!right}`
    },
    finishQuiz () {
      this.isQuizModalShown = false
      this.fetchStatus()
      // Show result
    },
    continueQuizHandler () {
      if (this.isQuestionActive) {
        this.handInQuestion()
        return
      }
      this.showNextQuestion()
    },
    async sendCommentHandler () {
      await commentQuestion(this.question.id, this.comment)
      this.commentSectionVisible = false
      this.$refs['comment-collapse-toggle'].disabled = true
      pulseSuccess(this.$i18n('quiz.comment.sent'))
    },
    pauseQuiz () {
      this.isQuizModalShown = false
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

  &.success {
    background-color: var(--fs-color-success-500);
  }
  &.failiure {
    background-color: var(--fs-color-danger-500);
  }
  &.neutral {
    background-color: var(--fs-color-info-300);
  }
}

#comment-collapse {
  text-align: right;

  .btn{
    margin-top: .5em;
  }
}

.explanation {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 20em;
}

.show-full-expl {
  position: relative;
  top: -5px;
}

</style>
