<template>
  <div v-if="results">
    <br>
    <h5>
      {{ $i18n(`quiz.results.title.${results.status}`) }}
    </h5>
    <p>
      {{ $i18n(`quiz.results.points.${results.status}`, results) }}
    </p>

    <p v-if="!results.details">
      <i>Die ganaue Auswertung wird nur für 2 Wochen nach dem Quiz gespeichert und ist nicht mehr verfügbar.</i>
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
          <p>
            <b>Antworten:</b>
            <span
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
                <ExpandableExplanation
                  :text="answer.explanation"
                />
              </span>
            </span>
          </p>
          <p>
            <a :href="externalLink(result.wikilink)">Weitere Infos dazu im Wiki</a>
          </p>
          <p>
            <QuestionCommentField
              :question-id="result.id"
            />
          </p>
        </b-card-body>
      </b-collapse>
    </div>
  </div>
</template>

<script>

import QuestionCommentField from './QuestionCommentField'
import ExpandableExplanation from './ExpandableExplanation'

export default {
  components: {
    QuestionCommentField,
    ExpandableExplanation,
  },
  props: {
    results: {
      type: Object,
      default: () => {},
    },
  },
  data () {
    return {
    }
  },
  methods: {
    answerColorClass (right) {
      return ['failiure', 'success', 'neutral'][right]
    },
    externalLink (url) {
      if (!url.startsWith('http')) {
        url = 'https://'
      }
      return url
    },
  },
}
</script>

<style lang="scss">

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

  &:not(.neutral) a {
    color: currentColor;
  }
}

.mistake-icon {
  float: right;
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
