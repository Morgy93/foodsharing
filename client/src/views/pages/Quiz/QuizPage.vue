<template>
  <Container
    v-if="status"
    :title="title"
    :collapsible="false"
    :wrap-content="true"
  >
    <!-- Quiz description -->
    <div v-html="quiz.desc" />

    <!-- State based info -->
    <div v-if="!isQuizModalShown">
      <hr>
      <p>{{ stateBasedInfo }}</p>
    </div>

    <!-- Buttons -->
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

      <div v-if="isQuizRunning">
        <b-button
          block
          variant="primary"
          @click="showStartModal"
        >
          {{ $i18n('quiz.continuenow') }}
        </b-button>
      </div>
    </div>

    <!-- Results section -->
    <div v-if="canViewResults">
      <hr>
      <b-button
        block
        variant="secondary"
        @click="displayResults()"
      >
        Ergebnisse des letzten Versuchs ansehen
      </b-button>

      <b-collapse
        v-model="showResults"
      >
        <QuizResults
          :results="results"
        />
      </b-collapse>
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
    <QuizModal
      ref="quizModal"
      :visible="isQuizModalShown"
      :quiz="quiz"
      :status="status"
      @update:questions-answered="(answered) => status.answered = answered"
      @update:visible="(visible) => isQuizModalShown = visible"
      @fetch-status="fetchStatus"
      @finished-quiz="onFinishedQuiz"
    />
  </Container>
</template>

<script>

import Container from '@/components/Container/Container.vue'
import i18n from '@/helper/i18n'
import { getQuizStatus, startQuiz, getQuizResults } from '@/api/quiz'
import QuizModal from './QuizModal'
import QuizResults from './QuizResults'

const QUIZ_STATUS = {
  neverTried: 0,
  running: 1,
  passed: 2,
  failed: 3,
  pause: 4,
  pauseElapsed: 5,
  disqualified: 6,
}

export default {
  components: {
    Container,
    QuizModal,
    QuizResults,
  },
  props: {
    quiz: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      infoKeys: ['wiki', 'real_life_examples', 'limited_tries', 'alone', 'read_carefully', 'multiple_choice', 'comment', 'pause', 'feedback'],
      timed: undefined,
      isFetching: false,
      status: null,
      isQuizModalShown: false,
      timeOutTimer: null,
      console: window.console, // TODO remove
      results: null,
      showResults: false,
    }
  },
  computed: {
    canStart () {
      return [QUIZ_STATUS.neverTried, QUIZ_STATUS.failed, QUIZ_STATUS.pauseElapsed].includes(this.status.status)
    },
    canViewResults () {
      return ![QUIZ_STATUS.neverTried, QUIZ_STATUS.running].includes(this.status.status)
    },
    isQuizRunning () {
      return this.status.status === QUIZ_STATUS.running
    },
    statusName () {
      return Object.keys(QUIZ_STATUS).find(key => QUIZ_STATUS[key] === this.status.status)
    },
    title () {
      return i18n(`quiz.title.${this.statusName}`, this.quiz)
    },
    stateBasedInfo () {
      if (!this.statusName) return ''
      if ([QUIZ_STATUS.failed, QUIZ_STATUS.pauseElapsed].includes(this.status.status)) {
        return i18n(`quiz.state_based_info.${this.statusName}.${this.status.tries}`)
      }
      return i18n(`quiz.state_based_info.${this.statusName}`, this.status)
    },
  },
  mounted: function () {
    this.fetchStatus()
  },
  methods: {
    async fetchStatus (resolve) {
      this.status = await getQuizStatus(this.quiz.id)
      resolve?.()
    },
    async fetchResults () {
      this.results = await getQuizResults(this.quiz.id)
    },
    async initQuiz () {
      if (!this.isQuizRunning) {
        await startQuiz(this.quiz.id, this.timed)
      }
      this.$refs.quizModal.showNextQuestion()
      this.results = null
    },
    showStartModal (timed) {
      this.timed = timed
      this.$refs['start-info-modal'].show()
    },
    answerColorClass (right, selected = true) {
      if (right === 2) return 'neutral'
      if (!right ^ selected) return 'success'
      return 'failiure'
    },
    async displayResults (visibility = !this.showResults) {
      if (!this.results) {
        await this.fetchResults()
      }
      this.showResults = visibility
    },
    onFinishedQuiz () {
      this.results = null
      this.displayResults(true)
    },
  },
}
</script>

<style lang="scss" scoped>
</style>
