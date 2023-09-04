<template>
  <Container
    v-if="quiz"
    :title="$i18n('quiz.general.title')"
    :wrap-content="true"
  >
    <p>
      <b v-text="$i18n('quiz.key_facts.to_pass')" />
      <span v-text="quizKeyFacts" />
    </p>

    <p>
      <b v-text="$i18n('desc')+':'" />
      <span v-html="quiz.desc" />
    </p>

    <div
      v-if="canEdit"
    >
      <hr>
      <b-button
        block
        variant="primary"
        @click="$bvModal.show('quizBasicsInputModal')"
      >
        {{ $i18n('quiz.general.edit') }}
      </b-button>
      <QuizBasicsInputModal
        :quiz="quiz"
      />
    </div>
  </Container>
</template>

<script>
import Container from '@/components/Container/Container.vue'
import { getQuestions, getQuiz } from '@/api/quiz'
import i18n from '@/helper/i18n'
import QuizBasicsInputModal from './QuizBasicsInputModal.vue'

export default {
  components: {
    Container,
    QuizBasicsInputModal,
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
      quiz: null,
      questions: null,
    }
  },
  computed: {
    quizKeyFacts () {
      const params = Object.assign({}, this.quiz, { questcountEasymode: this.quiz.questcount * 2 })
      return i18n(`quiz.key_facts.${this.quiz.easymode ? 'easy' : 'hard'}mode`, params)
    },
  },
  mounted: function () {
    this.fetchQuizDetails()
  },
  methods: {
    async fetchQuizDetails () {
      [this.quiz, this.questions] = await Promise.all([
        getQuiz(this.quizId),
        getQuestions(this.quizId),
      ])
    },
  },
}

</script>
