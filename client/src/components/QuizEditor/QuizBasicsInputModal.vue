<template>
  <b-modal
    id="quizBasicsInputModal"
    :title="'Quiz bearbeiten'"
  >
    {{ form }}
    <b-form>
      <b-form-group
        label="Name:"
        label-for="name-input"
      >
        <b-form-input
          id="name-input"
          v-model="form.name"
          placeholder="Name des Quiz"
          required
        />
      </b-form-group>

      <b-form-group
        label="Maximale Fehlerpunkte zum Bestehen:"
        label-for="maxfp-input"
      >
        <b-form-input
          id="maxfp-input"
          v-model="form.maxfp"
          type="number"
          min="0"
          max="20"
          required
        />
      </b-form-group>

      <b-form-group
        label="Anzahl zu beantwortender Fragen:"
        label-for="questcount-input"
      >
        <b-form-input
          id="questcount-input"
          v-model="form.questcount"
          type="number"
          min="1"
          max="25"
          required
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
          v-model="form.questcountUntimed"
          type="number"
          :min="form.questcount"
          max="50"
          required
        />
      </b-form-group>

      <MarkdownInput
        :initial-value="form.desc"
        @update:value="newValue => form.desc = newValue"
      />
    </b-form>
  </b-modal>
</template>

<script>
import MarkdownInput from '@/components/Markdown/MarkdownInput.vue'

export default {
  components: { MarkdownInput },
  props: {
    quiz: {
      type: Object,
      required: true,
    },
  },
  data () {
    return {
      form: Object.assign({}, this.quiz),
      console: console,
    }
  },
}
</script>
