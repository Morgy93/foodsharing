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
        ref="results-collapse"
        v-model="showResults"
      >
        <div v-if="results">
          <br>
          <h5>
            {{ $i18n(`quiz.results.title.${results.status}`) }}
          </h5>
          <p>
            {{ $i18n(`quiz.results.points.${results.status}`, results) }}
          </p>

          <div
            v-for="(result, i) in results.details"
            :key="result.id"
            no-body
          >
            <b-card-header>
              <b-button
                v-b-toggle="`accordion-${i}`"
                block
                size="sm"
                class="result-detail-toggle"
              >
                <span>
                  Frage {{ i+1 }}
                </span>
                <b-badge
                  pill
                  :variant="result.userfp ? 'danger' : 'success'"
                  class="fp-counter"
                >
                  {{ result.userfp }} Fehler
                </b-badge>
              </b-button>
            </b-card-header>
            <b-collapse
              :id="`accordion-${i}`"
              accordion="results-accordion"
            >
              <b-card-body>
                <p>
                  <b>Frage:</b>
                  {{ result.text }}
                </p>
                <div>
                  <a href="#">Antworten</a>

                  <div
                    v-for="answer in [...result.answers].sort((a,b) => a.right-b.right)"
                    :key="answer.id"
                    class="result-answer-container"
                  >
                    <span
                      :class="answerColorClass(answer.right)"
                    >
                      <i
                        v-if="result.useranswers.includes(answer.id) ^ answer.right === 1"
                        v-b-tooltip="'Bei dieser Antwort hast du einen Fehler gemacht.'"
                        class="fas fa-exclamation-triangle mistake-icon"
                      />
                      <b>{{ ['Falsch', 'Richtig', 'Neutral'][answer.right] }}:</b>
                      {{ answer.text }}
                      <br>
                      <a href="#">Erklärung anzeigen</a>
                    </span>
                  </div>
                </div>
                <p>
                  <a :href="result.wikilink">Weitere Infos dazu im Wiki</a>
                </p>
                <p>
                  <a href="#">Kommentar schreiben</a>
                </p>
              </b-card-body>
            </b-collapse>
          </div>
        </div>
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
      @fetch-status="(resolve) => fetchStatus().then(() => resolve?.())"
    />
  </Container>
</template>

<script>

import Container from '@/components/Container/Container.vue'
import i18n from '@/helper/i18n'
import { getQuizStatus, startQuiz, getQuizResults } from '@/api/quiz'
import QuizModal from './QuizModal'

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
      switch (this.status.status) {
        case QUIZ_STATUS.failed:
        case QUIZ_STATUS.pauseElapsed:
          return i18n(`quiz.state_based_info.${this.statusName}.${this.status.tries}`)
        default:
          return i18n(`quiz.state_based_info.${this.statusName}`, this.status)
      }
    },
  },
  mounted: function () {
    this.fetchStatus()
  },
  methods: {
    async fetchStatus () {
      this.status = await getQuizStatus(this.quiz.id)
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
    async displayResults () {
      if (!this.results) {
        await this.fetchResults()
      }
      this.showResults = !this.showResults
    },
  },
}
</script>

<style lang="scss" scoped>

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

.fp-counter {
  float: right;
  top: .2em;
}

.result-detail-toggle span:nth-child(1) {
  float: left;
}

.result-answer-container>span {
  display: block;
  padding: .5em .75em;
  margin-bottom: .25em;
  border-radius: 1em;
  a {
    color: currentColor;
  }
}

.mistake-icon {
  float: right;
}

</style>
