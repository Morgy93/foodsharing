<template>
  <div>
    <div class="head ui-widget-header ui-corner-top">
      {{ quizName }}: {{ $i18n('settings.foodsaver_quiz.title') }}
    </div>
    <div class="bootstrap ui-widget-content corner-bottom margin-bottom ui-padding">
      <div>{{ $i18n('settings.foodsaver_quiz.text1') }}</div>
      <br>
      <div>{{ $i18n('settings.foodsaver_quiz.text2') }}</div>
      <b-container>
        <b-row
          class="mt-4 mb-4 justify-content-center"
        >
          <b-button
            variant="secondary"
            class="pl-5 pr-5"
            :href="$url('quizFoodsaverWiki')"
            target="_blank"
          >
            {{ $i18n('settings.foodsaver_quiz.goto_learn_infos_wiki') }}
          </b-button>
        </b-row>
        <b-row>
          <div>{{ $i18n('settings.foodsaver_quiz.help_text') }}</div>
        </b-row>

        <b-row
          class="mb-4 justify-content-center"
        >
          <b-button
            variant="secondary"
            class="pl-5 pr-5"
            :href="$url('quizFoodsaverWiki')"
            target="_blank"
          >
            {{ $i18n('settings.foodsaver_quiz.help_button') }}
          </b-button>
        </b-row>
        <h4>{{ $i18n('settings.foodsaver_quiz.start_quiz_title') }}</h4>
        <div>
          <b-row
            class="mb-4 justify-content-center"
          >
            <b-col
              :class="{'col-auto': quizId != 1, 'col-6': quizId == 1}"
            >
              <b-button
                v-b-modal.quizModalWithTime
                variant="secondary"
                class="pl-5 pr-5"
              >
                {{ $i18n('settings.foodsaver_quiz.quiz_time_limit') }}
              </b-button>
            </b-col>
            <b-col
              v-if="quizId == '1'"
              :class="{'col-12': quizId != 1, 'col-6': quizId == 1}"
            >
              <div>
                <b-button
                  v-b-modal.quizModalWithoutTime
                  variant="secondary"
                  class="pl-5 pr-5"
                >
                  {{ $i18n('settings.foodsaver_quiz.quiz_without_time_limit') }}
                </b-button>
                <b-modal
                  id="quizModalWithTime"
                  modal-class="bootstrap"
                  header-class="d-flex"
                  content-class="pr-3 pt-3"
                  :cancel-title="$i18n('button.cancel')"
                  :ok-title="$i18n('settings.quiz_modal.button_start')"
                  :title="quizName + ': '+ $i18n('settings.quiz_modal.title')"
                  @ok="startQuizWithTime"
                >
                  <QuizStartPopup />
                </b-modal>
                <b-modal
                  id="quizModalWithoutTime"
                  modal-class="bootstrap"
                  header-class="d-flex"
                  content-class="pr-3 pt-3"
                  :cancel-title="$i18n('button.cancel')"
                  :ok-title="$i18n('settings.quiz_modal.button_start')"
                  :title="quizName + ': '+ $i18n('settings.quiz_modal.title')"
                  @ok="startQuizWithoutTime"
                >
                  <QuizStartPopup />
                </b-modal>
              </div>
            </b-col>
          </b-row>
        </div>
      </b-container>
      <div>{{ $i18n('settings.foodsaver_quiz.quiz_hint') }}</div>
    </div>
  </div>
</template>

<script>
import QuizStartPopup from './QuizStartPopup'
import { ajreq } from '@/script'

export default {
  components: { QuizStartPopup },
  props: {
    quizId: { type: Number, default: null },
    quizName: { type: String, default: '' },
  },
  methods: {
    startQuizWithTime () {
      ajreq('startquiz', { app: 'quiz', qid: this.quizId })
    },
    startQuizWithoutTime () {
      ajreq('startquiz', { app: 'quiz', easymode: 1, qid: this.quizId })
    },
  },
}
</script>

<style scoped>

</style>
