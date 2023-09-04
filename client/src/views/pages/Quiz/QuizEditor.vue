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
        <QuizMetadataEditor
          :quiz-id="quizId"
          :can-edit="canEdit"
        />
      </div>
    </div>
  </section>
</template>

<script>
import QuizMetadataEditor from '@/components/QuizEditor/QuizBasicsSection.vue'
import QuizSelection from '@/components/QuizEditor/QuizSelection.vue'

export default {
  components: {
    QuizMetadataEditor,
    QuizSelection,
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
