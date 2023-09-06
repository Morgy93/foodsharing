<template>
  <b-modal
    :id="id"
    :title="'Frage bearbeiten'"
    :ok-disabled="!valuesValid"
    scrollable
    centered
    @ok="handleOk"
    @show="() => form = Object.assign({}, answer)"
  >
    <b-form ref="form">
      <b-form-group
        label="Antwort:"
        label-for="text-input"
      >
        <b-form-textarea
          id="text-input"
          v-model="form.text"
          placeholder="Antwort..."
          required
          :state="!!form.text"
          trim
          rows="3"
        />
      </b-form-group>

      <b-form-group
        label="Erklärung:"
        label-for="explanation-input"
      >
        <b-form-textarea
          id="explanation-input"
          v-model="form.explanation"
          placeholder="Erklärung..."
          required
          :state="!!form.explanation"
          trim
          rows="3"
        />
      </b-form-group>

      <b-form-group
        label="Wertung"
        label-for="right-input"
      >
        <b-form-select
          id="right-input"
          v-model.number="form.right"
          :options="rightOptions"
        />
      </b-form-group>
    </b-form>
  </b-modal>
</template>

<script>
import { editAnswer, addAnswer } from '@/api/quiz'

export default {
  props: {
    answer: {
      type: Object,
      required: true,
    },
    id: {
      type: String,
      default: 'editAnswerModal',
    },
    questionId: {
      type: Number,
      default: -1,
    },
  },
  data () {
    return {
      form: {},
      rightOptions: [0, 1, 2].map(x => ({ text: this.$i18n('quiz.answers.short.' + x), value: x })),
    }
  },
  computed: {
    valuesValid () {
      return this.form.text &&
        this.form.explanation
    },
  },
  methods: {
    async handleOk () {
      if (this.answer.id) {
        await editAnswer(this.answer.id, this.form)
      } else {
        await addAnswer(this.questionId, this.form)
      }
      this.$emit('update')
    },
  },
}
</script>
