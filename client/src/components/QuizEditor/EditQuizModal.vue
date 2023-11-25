<template>
  <b-modal
    id="editQuizModal"
    :title="'Quiz bearbeiten'"
    :ok-disabled="!valuesValid"
    scrollable
    centered
    @ok="handleOk"
  >
    <b-form ref="form">
      <b-form-group
        label="Name:"
        label-for="name-input"
      >
        <b-form-input
          id="name-input"
          v-model="form.name"
          placeholder="Name des Quiz"
          required
          :state="!!form.name"
          trim
        />
      </b-form-group>

      <b-form-group
        label="Maximale Fehlerpunkte zum Bestehen:"
        label-for="maxfp-input"
      >
        <b-form-input
          id="maxfp-input"
          v-model.number="form.maxfp"
          type="number"
          min="0"
          max="20"
          required
          :state="form.maxfp !== ''"
        />
      </b-form-group>

      <b-form-group
        label="Anzahl zu beantwortender Fragen:"
        label-for="questcount-input"
      >
        <b-form-input
          id="questcount-input"
          v-model.number="form.questcount"
          type="number"
          min="1"
          max="25"
          required
          :state="form.questcount !== ''"
        />
      </b-form-group>

      <b-form-group>
        <b-form-checkbox
          v-model="form.easymode"
        >
          Bearbeitung ohne Zeitbegrenzung erlauben
        </b-form-checkbox>
      </b-form-group>

      <b-form-group
        v-if="form.easymode"
        label="Anzahl zu beantwortender Fragen ohne Zeitbegrenzung:"
        label-for="questcount-input"
      >
        <b-form-input
          id="questcount-input"
          v-model.number="form.questcount_untimed"
          type="number"
          :min="form.questcount"
          max="50"
          required
          :state="form.questcount !== '' && form.questcount <= form.questcount_untimed"
        />
      </b-form-group>

      <b-alert
        v-if="quiz.is_desc_htmlentity_encoded"
        variant="danger"
        show
      >
        <b>Achtung:</b>
        Die Formatierung der Beschreibung wurde verändert. Was du im Texteingabefeld siehst, ist mit html formatiert. Bitte verändere die Beschreibung, sodass sie Markdown nutzt. Bitte nutze die Vorschaufunktion des Eingabefelds um sicherzustellen, dass die Formatierung korrekt angepasst wurde.
      </b-alert>

      <MarkdownInput
        :value="form.desc"
        :state="!form.desc.includes('<')"
        :rows="6"
        @update:value="newValue => form.desc = newValue"
      />
    </b-form>
  </b-modal>
</template>

<script>
import MarkdownInput from '@/components/Markdown/MarkdownInput.vue'
import { editQuiz } from '@/api/quiz'

export default {
  components: { MarkdownInput },
  props: {
    quiz: {
      type: Object,
      required: true,
    },
  },
  data () {
    const form = Object.assign({}, this.quiz)
    if (form.questcount_untimed) {
      form.easymode = true
    }
    return {
      form,
    }
  },
  computed: {
    valuesValid () {
      return this.form.name &&
        typeof (this.form.maxfp) === 'number' &&
        typeof (this.form.questcount) === 'number' &&
        (!this.form.easymode || (typeof (this.form.questcount_untimed) === 'number' && this.form.questcount <= this.form.questcount_untimed)) &&
        !this.form.desc.includes('<')
    },
  },
  methods: {
    async handleOk () {
      // Send data
      const quizData = Object.assign({}, this.form)
      console.log(quizData)
      if (!quizData.easymode) {
        delete quizData.questcount_untimed
      }
      delete quizData.easymode
      delete quizData.is_desc_htmlentity_encoded
      console.log(quizData)
      await editQuiz(this.quiz.id, quizData)
      this.$emit('update')
    },
  },
}
</script>
