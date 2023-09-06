<template>
  <Container
    v-if="questions"
    :title="$i18n('quiz.questions_section.title', {count: questions.length})"
    :wrap-content="true"
  >
    <div
      v-for="(question, i) in questions"
      :key="question.id"
      no-body
    >
      <b-card-header>
        <b-button
          v-b-toggle="`accordion-${i}`"
          block
          class="result-detail-toggle"
        >
          <span>
            {{ $i18n(`quiz.question`) }} {{ i+1 }}
          </span>

          <OverflowMenu
            :options="[
              {hide: !canEdit, icon:'pen', textKey: 'quiz.question_options.edit', callback: () => console.log(1)},
              {hide: !canEdit, icon:'plus-circle', textKey: 'quiz.question_options.add_answer', callback: () => console.log(4)},
              {hide: !question.comment_count, icon:'comments', textKey: 'quiz.question_options.show_comments', callback: () => console.log(2)},
              {hide: !canEdit, icon:'trash', textKey: 'quiz.question_options.delete', callback: () => console.log(3)},
            ]"
          />

          <b-badge
            v-if="question.comment_count"
            pill
            class="fp-counter"
          >
            {{ question.comment_count }}
            <i class="fas fa-comments" />
          </b-badge>
        </b-button>
      </b-card-header>
      <b-collapse
        :id="`accordion-${i}`"
        accordion="results-accordion"
      >
        <b-card-body>
          <p>
            <b>{{ $i18n(`quiz.question`) }}:</b>
            {{ question.text }}
          </p>
          <p>
            <b>{{ $i18n(`quiz.timelimit`) }}:</b>
            {{ question.duration + 's' }}
          </p>
          <p>
            <b>{{ $i18n('fp') }}:</b>
            {{ $i18n('quiz.fp_description', {fp: question.fp, per_mistake: Math.round(1e2 * question.fp / question.answers.length) / 1e2 }) }}
          </p>
          <p>
            <b>{{ $i18n('wikilink') }}:</b>
            <a :href="question.wikilink">{{ question.wikilink }}</a>
          </p>
          <p>
            <b>{{ $i18n(`quiz.answers.name`) }}:</b>
            <span
              v-for="answer in [...question.answers].sort((a,b) => a.right-b.right)"
              :key="answer.id"
              class="result-answer-container"
            >
              <span
                :class="answerColorClass(answer.right)"
              >
                <OverflowMenu
                  :options="[
                    {hide: !canEdit, icon:'pen', textKey: 'quiz.answer_options.edit', callback: () => console.log(1)},
                  ]"
                />

                <b>{{ $i18n(`quiz.answers.short.${answer.right}`) }}:</b>
                {{ answer.text }}
                <br>
                <b>{{ $i18n('explanation') }}:</b>
                <span v-text="answer.explanation" />
              </span>
            </span>
          </p>
        </b-card-body>
      </b-collapse>
    </div>
  </Container>
</template>

<script>
import { getQuestions } from '@/api/quiz'
import Container from '@/components/Container/Container.vue'
import OverflowMenu from '@/components/OverflowMenu.vue'

export default {
  components: {
    Container,
    OverflowMenu,
  },
  props: {
    quizId: {
      type: Number,
      required: true,
    },
    canEdit: {
      type: Boolean,
      required: true,
    },
  },
  data () {
    return {
      questions: null,
      console: console,
    }
  },
  mounted: function () {
    this.fetchQuestions()
  },
  methods: {
    async fetchQuestions () {
      this.questions = await getQuestions(this.quizId)
    },
    answerColorClass (right) {
      return ['failiure', 'success', 'neutral'][right]
    },
  },
}
</script>

<style scoped lang="scss">

.badge.fp-counter {
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

.answer-edit {
  float: right;
  color: white !important;
  border-color: white  !important;
  background-color: transparent;

  &:hover {
    background-color: #fff2;
  }
  &:active {
    background-color: #fffa;
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
