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
          variant="outline-primary"
        >
          <span class="question-title">
            {{ $i18n(`quiz.question`) }} #{{ question.id }}
            -
            {{ question.text }}
          </span>

          <b-badge
            v-if="question.comment_count"
            class="comment-badge"
          >
            {{ question.comment_count }}
            <i class="fas fa-comments" />
          </b-badge>

          <OverflowMenu
            :options="[
              {hide: !canEdit, icon:'pen', textKey: 'quiz.question_options.edit', callback: () => $bvModal.show(`editQuestionModal-${i}`)},
              {hide: !canEdit, icon:'plus-circle', textKey: 'quiz.question_options.add_answer', callback: () => addAnswerHandler(`addAnswerModal-${i}`)},
              {hide: !question.comment_count, icon:'comments', textKey: 'quiz.question_options.show_comments', callback: () => console.log(question)},
              {hide: !canEdit, icon:'trash', textKey: 'quiz.question_options.delete', callback: () => deleteQuestionHandler(question.id)},
            ]"
            :float="false"
          />

          <EditQuestionModal
            :id="`editQuestionModal-${i}`"
            :question="question"
            @update="fetchQuestions()"
          />

          <EditAnswerModal
            :id="`addAnswerModal-${i}`"
            :answer="newAnswer"
            :question-id="question.id"
            @update="fetchQuestions()"
          />
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
                    {hide: !canEdit, icon:'pen', textKey: 'quiz.answer_options.edit', callback: () => $bvModal.show(`editAnswerModal-${answer.id}`)},
                    {hide: !canEdit, icon:'trash', textKey: 'quiz.answer_options.delete', callback: () => deleteAnswerHandler(answer.id)},
                  ]"
                />

                <EditAnswerModal
                  :id="`editAnswerModal-${answer.id}`"
                  :answer="answer"
                  @update="fetchQuestions()"
                />

                <b>{{ $i18n(`quiz.answers.short.${answer.right}`) }}:</b>
                {{ answer.text }}
                <br>
                <b>{{ $i18n('explanation') }}:</b>
                <span v-text="answer.explanation" />
              </span>
            </span>
          </p>
          <!-- <Wall
            target="bezirk"
            :target-id="3818"
          /> -->
        </b-card-body>
      </b-collapse>
    </div>

    <div
      v-if="canEdit"
    >
      <hr>
      <b-button
        block
        variant="primary"
        @click="$bvModal.show('addQuestionModal')"
      >
        Frage hinzufügen
      </b-button>

      <EditQuestionModal
        :id="`addQuestionModal`"
        :question="newQuestion"
        :quiz-id="quizId"
        @update="fetchQuestions()"
      />
    </div>
  </Container>
</template>

<script>
import { deleteAnswer, deleteQuestion, getQuestions } from '@/api/quiz'
import Container from '@/components/Container/Container.vue'
import OverflowMenu from '@/components/OverflowMenu.vue'
import EditQuestionModal from './EditQuestionModal.vue'
import EditAnswerModal from './EditAnswerModal.vue'

export default {
  components: {
    Container,
    OverflowMenu,
    EditQuestionModal,
    EditAnswerModal,
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
      newAnswer: {
        text: '',
        explanation: '',
        right: 1,
      },
      newQuestion: {
        text: '',
        fp: 1,
        duration: 120,
        wikilink: '',
      },
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
    async deleteQuestionHandler (questionId) {
      const confirmed = await this.$bvModal.msgBoxConfirm('Willst du diese Frage wirklich löschen?', {
        title: 'Sicher?',
        okVariant: 'danger',
        okTitle: 'Löschen',
        cancelTitle: 'Abbrechen',
        centered: true,
      })
      if (confirmed) {
        await deleteQuestion(questionId)
        await this.fetchQuestions()
      }
    },
    async deleteAnswerHandler (answerId) {
      const confirmed = await this.$bvModal.msgBoxConfirm('Willst du diese Antwort wirklich löschen?', {
        title: 'Sicher?',
        okVariant: 'danger',
        okTitle: 'Löschen',
        cancelTitle: 'Abbrechen',
        centered: true,
      })
      if (confirmed) {
        await deleteAnswer(answerId)
        await this.fetchQuestions()
      }
    },
    addAnswerHandler (id) {
      this.$bvModal.show(id)
    },
  },
}
</script>

<style scoped lang="scss">
.question-title {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  flex-grow: 1;
  text-align: left;
}
.comment-badge {
  line-height: normal;
}
.result-detail-toggle {
  display: flex;
  &:hover .overflow-menu {
    color: white;
  }
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
  .overflow-menu {
    color: white;
  }
}
.failiure {
  background-color: var(--fs-color-danger-500);
  color:white;
  .overflow-menu {
    color: white;
  }
}
.neutral {
  background-color: var(--fs-color-warning-200);
}

</style>
