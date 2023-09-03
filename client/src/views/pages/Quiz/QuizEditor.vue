<template>
  <section class="container my-3 my-sm-5">
    <div class="row">
      <div class="col-sm-3">
        {{ visibleQuizes }}
        <QuizSelection
          :visible-quizes="visibleQuizes"
        />
      </div>
      <div class="col-sm-9">
        <QuizMetadataEditor
          :quiz-id="quizId"
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
    initialQuizId: {
      type: Number,
      required: true,
    },
    visibleQuizes: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      quizId: this.initialQuizId,
    }
  },
}
</script>

<style></style>
