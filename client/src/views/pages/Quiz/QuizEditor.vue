<template>
  <section class="container my-3 my-sm-5">
    <div class="row">
      <!-- TODO basic page layout needs improvement -->
      <div class="col-lg-3">
        <QuizSelection
          :visible-quizes="visibleQuizes"
        />
      </div>
      <div class="col-lg-9">
        <QuizBasicsSection
          :quiz-id="quizId"
          :can-edit="canEdit"
        />

        <QuestionsSection
          :quiz-id="quizId"
          :can-edit="canEdit"
        />
      </div>
    </div>
  </section>
</template>

<script>
import QuizSelection from '@/components/QuizEditor/QuizSelection.vue'
import QuestionsSection from '@/components/QuizEditor/QuestionsSection.vue'
import QuizBasicsSection from '@/components/QuizEditor/QuizBasicsSection.vue'

export default {
  components: {
    QuizSelection,
    QuizBasicsSection,
    QuestionsSection,
  },
  props: {
    quizId: {
      type: Number,
      required: true,
    },
    visibleQuizes: {
      type: Array,
      required: true,
    },
  },
  data () {
    return {
      canEdit: this.visibleQuizes.find(quiz => quiz.id === this.quizId).edit,
    }
  },
}
</script>

<style></style>
