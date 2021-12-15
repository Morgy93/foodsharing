<template>
  <div>
    <div class="head ui-widget-header ui-corner-top">
      {{ quizName }}: {{ $i18n('settings.foodsaver_quiz.title') }}
    </div>
    <div class="bootstrap ui-widget-content corner-bottom margin-bottom ui-padding">
      <div>{{ $i18n('settings.foodsaver_quiz.text1') }}</div>
      <br>
      <div>{{ $i18n('settings.foodsaver_quiz.text2') }}</div>
      <br>
      <div>
        <b-button
          variant="secondary"
          class="pl-5 pr-5"
          :href="$url('quizFoodsaverWiki')"
          target="_blank"
        >
          {{ $i18n('settings.foodsaver_quiz.goto_learn_infos_wiki') }}
        </b-button>
      </div>
      <br>
      <div>{{ $i18n('settings.foodsaver_quiz.help_text') }}</div>
      <br>
      <div>
        <b-button
          variant="secondary"
          class="pl-5 pr-5"
          :href="$url('quizFoodsaverWiki')"
          target="_blank"
        >
          {{ $i18n('settings.foodsaver_quiz.help_button') }}
        </b-button>
      </div>
      <br>
      <h4>{{ $i18n('settings.foodsaver_quiz.start_quiz_title') }}</h4>
      <div>
        <b-container>
          <b-row>
            <b-col
              cols="12"
              lg="6"
              class="mb-4"
            >
              <b-button
                v-b-modal.modal-1
                variant="secondary"
                class="pl-5 pr-5"
              >
                {{ $i18n('settings.foodsaver_quiz.quiz_time_limit') }}
              </b-button>
            </b-col>
            <b-col>
              <div>
                <b-button
                  v-b-modal.modal-1
                  variant="secondary"
                  class="pl-5 pr-5"
                >
                  {{ $i18n('settings.foodsaver_quiz.quiz_without_time_limit') }}
                </b-button>
                <b-modal
                  id="modal-1"
                  modal-class="bootstrap"
                  header-class="d-flex"
                  content-class="pr-3 pt-3"
                  :cancel-title="$i18n('button.cancel')"
                  :ok-title="$i18n('settings.quiz_modal.button_start')"
                  :title="quizName + ': '+ $i18n('settings.quiz_modal.title')"
                  @ok="startQuiz"
                >
                  <p>
                    <i class="fas fa-glasses" /> {{ $i18n('settings.quiz_modal.before_title') }}
                  </p>
                  <ul class="mb-4">
                    <li>{{ $i18n('settings.quiz_modal.before_text') }}</li>
                  </ul>
                  <p>
                    <i class="fas fa-question" /> {{ $i18n('settings.quiz_modal.during_title') }}
                  </p>
                  <ul class="mb-4">
                    <li>{{ $i18n('settings.quiz_modal.during_text_1') }}</li>
                    <li>{{ $i18n('settings.quiz_modal.during_text_2') }}</li>
                    <li>{{ $i18n('settings.quiz_modal.during_text_3') }}</li>
                    <li>{{ $i18n('settings.quiz_modal.during_text_4') }}</li>
                  </ul>
                  <p>
                    <i class="fas fa-check-circle" /> {{ $i18n('settings.quiz_modal.after_title') }}
                  </p>
                  <ul>
                    <li>{{ $i18n('settings.quiz_modal.after_text_1') }}</li>
                    <li>{{ $i18n('settings.quiz_modal.after_text_2') }}</li>
                  </ul>
                  <p>
                    {{ $i18n('settings.quiz_modal.hint_title') }}:<br>
                    {{ $i18n('settings.quiz_modal.hint_text') }}
                  </p>
                </b-modal>
              </div>
            </b-col>
          </b-row>
        </b-container>
        <div>{{ $i18n('settings.foodsaver_quiz.quiz_hint') }}</div>
      </div>
    </div>
  </div>
</template>

<script>
import { ajreq } from '@/script'

export default {
  props: {
    quizId: { type: Number, default: null },
    quizName: { type: String, default: '' },
  },
  methods: {
    startQuiz () {
      ajreq('startquiz', { app: 'quiz', qid: this.quizId })
    },
  },
}
</script>

<style scoped>

</style>
