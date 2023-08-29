<template>
  <Container
    title="Quiz Bearbeiten"
    :collapsible="false"
    :wrap-content="true"
  >
    {{ quiz }}

    {{ questions }}
  </Container>
</template>

<script>
import Container from '@/components/Container/Container.vue'
import { getQuestions, getQuiz } from '@/api/quiz'

export default {
  components: {
    Container,
  },
  props: {
    initialQuizId: {
      type: Number,
      required: true,
    },
  },
  data () {
    return {
      quizId: this.initialQuizId,
      quiz: null,
      questions: null,
    }
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

<style>

</style>
