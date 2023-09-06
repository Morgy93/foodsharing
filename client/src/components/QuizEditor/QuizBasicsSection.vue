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
      <span
        v-if="quiz.is_desc_htmlentity_encoded"
        v-html="quiz.desc"
      />
      <Markdown
        v-else
        :source="quiz.desc"
      />
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
        @update="fetchQuiz()"
      />
    </div>
  </Container>
</template>

<script>
import Container from '@/components/Container/Container.vue'
import { getQuiz } from '@/api/quiz'
import i18n from '@/helper/i18n'
import QuizBasicsInputModal from './QuizBasicsInputModal.vue'
import Markdown from '@/components/Markdown/Markdown.vue'

export default {
  components: {
    Container,
    QuizBasicsInputModal,
    Markdown,
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
    }
  },
  computed: {
    quizKeyFacts () {
      return i18n(`quiz.key_facts.${this.quiz.questcount_untimed ? 'easy' : 'hard'}mode`, this.quiz)
    },
  },
  mounted: function () {
    this.fetchQuiz()
  },
  methods: {
    async fetchQuiz () {
      this.quiz = await getQuiz(this.quizId)
    },
  },
}

</script>
