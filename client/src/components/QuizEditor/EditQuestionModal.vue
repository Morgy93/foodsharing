<template>
  <b-modal
    :id="id"
    :title="'Frage bearbeiten'"
    :ok-disabled="!valuesValid"
    scrollable
    @ok="handleOk"
  >
    <b-form ref="form">
      <b-form-group
        label="Frage:"
        label-for="text-input"
      >
        <b-form-textarea
          id="text-input"
          v-model="form.text"
          placeholder="Quizfrage..."
          required
          :state="!!form.text"
          trim
          rows="3"
        />
      </b-form-group>

      <b-form-group
        label="Zeit zum Antworten"
        label-for="duration-input"
      >
        <b-form-select
          id="duration-input"
          v-model.number="form.duration"
          :options="durationOptions"
        />
      </b-form-group>

      <b-form-group
        label="Fehlerpunkte"
        label-for="fp-input"
      >
        <b-form-select
          id="fp-input"
          v-model.number="form.fp"
          :options="fpOptions"
        />
      </b-form-group>

      <b-form-group
        label="Link zum passenden Wikiartikel:"
        label-for="wikilink-input"
      >
        <b-form-input
          id="wikilink-input"
          v-model="form.wikilink"
          placeholder="https://wiki.foodsharing.de/..."
          required
          :state="form.wikilink.startsWith('https://wiki.foodsharing.de/')"
          trim
        />
      </b-form-group>
    </b-form>
  </b-modal>
</template>

<script>
import { editQuestion } from '@/api/quiz'

export default {
  props: {
    question: {
      type: Object,
      required: true,
    },
    id: {
      type: String,
      default: 'editQuestionModal',
    },
  },
  data () {
    const form = Object.assign({}, this.question)
    delete form.answers
    delete form.comment_count
    return {
      form,
      durationOptions: this.buildDurationOptions(),
      fpOptions: this.buildFpOptions(),
    }
  },
  computed: {
    valuesValid () {
      return this.form.text &&
        this.form.duration &&
        this.form.wikilink.startsWith('https://wiki.foodsharing.de/')
    },
  },
  methods: {
    async handleOk () {
      await editQuestion(this.question.id, this.form)
      this.$emit('update')
    },
    buildDurationOptions () {
      const durationOptions = []
      for (let i = 10; i <= 180; i += 10) {
        const [min, sec] = [Math.floor(i / 60), i % 60]
        let text = ''
        if (min) {
          text = `${min} ${this.$i18n('timepicker.labelMinutes')}`
          if (sec) {
            text += ', '
          }
        }
        if (sec) {
          text += `${sec} ${this.$i18n('timepicker.labelSeconds')}`
        }
        durationOptions.push({ value: i, text })
      }
      return durationOptions
    },
    buildFpOptions () {
      return [0, 1, 2, 3, 12].map(fp => ({
        value: fp,
        text: this.$i18n('quiz.fp_options.' + ({ 0: 'joke', 12: 'ko' }[fp] || 'default'), { fp }),
      }))
    },
  },
}
</script>
