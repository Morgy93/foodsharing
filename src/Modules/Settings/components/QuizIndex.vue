<template>
  <div>
    <div class="head ui-widget-header ui-corner-top">
      <span
        v-if="quizState === 0"
      >
        {{ quizName }}: {{ $i18n('settings.foodsaver_quiz.title') }}</span>
      <span
        v-if="quizState === 1"
      >{{ quizName }}: {{ $i18n('quiz.continuetype') }}
      </span>
      <span
        v-else
      >{{ quizName }}: {{ $i18n('quiz.quizleft') }}
      </span>
    </div>
    <div class="bootstrap ui-widget-content corner-bottom margin-bottom ui-padding">
      <div
        v-if="quizState === 0"
      >
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

          <b-alert
            variant="dark"
            show
            class="mb-4 justify-content-center"
          >
            <b-row>
              <div>{{ $i18n('settings.foodsaver_quiz.help_text') }}</div>
            </b-row>
            <b-row
              class="mb-4 justify-content-center"
            >
              <b-button
                variant="outline-secondary"
                class="pl-5 pr-5"
                :href="$url('quizFoodsaverWiki')"
                target="_blank"
              >
                {{ $i18n('settings.foodsaver_quiz.help_button') }}
              </b-button>
            </b-row>
          </b-alert>
          <h4>{{ $i18n('settings.foodsaver_quiz.start_quiz_title') }}</h4>
          <div>
            <b-row
              class="mb-4 justify-content-center"
            >
              <b-col
                :class="{'col-auto': quizId != 1, 'col-6': quizId == 1}"
              >
                <b-button
                  variant="secondary"
                  class="pl-5 pr-5"
                  @click="showModal(true)"
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
                    variant="secondary"
                    class="pl-5 pr-5"
                    @click="showModal(false)"
                  >
                    {{ $i18n('settings.foodsaver_quiz.quiz_without_time_limit') }}
                  </b-button>
                </div>
              </b-col>
            </b-row>
          </div>
        </b-container>
        <div>{{ $i18n('settings.foodsaver_quiz.quiz_hint') }}</div>
      </div>
      <div
        v-if="quizState === 1"
      >
        <b-container>
          <b-row>
            <div>{{ $i18n('quiz.notfinishedyet') }}</div>
            <div>{{ $i18n('quiz.safeandsound') }}</div>
          </b-row>
          <b-row
            class="mt-4 mb-4 justify-content-center"
          >
            <b-button
              variant="secondary"
              class="pl-5 pr-5"
              @click="showModal(false)"
            >
              {{ $i18n('quiz.continuenow') }}
            </b-button>
          </b-row>
        </b-container>
      </div>
      <div
        v-else
      >
        <b-container>
          <b-row>
            <div>{{ $i18n('quiz.trynumber') }} {{ failedCount + 1 }}</div>
          </b-row>
          <b-row>
            <div>{{ failedCount }} {{ $i18n('quiz.failedbeforebut') }} {{ failedCount -1 }}</div>
          </b-row>
          <b-row
            class="mt-4 mb-4 justify-content-center"
          >
            <b-button
              variant="secondary"
              class="pl-5 pr-5"
              @click="showModal(false)"
            >
              {{ $i18n('quiz.continuenow') }}
            </b-button>
          </b-row>
        </b-container>
      </div>
    </div>
    <b-modal
      id="quizModal"
      ref="quizModal"
      modal-class="bootstrap"
      header-class="d-flex"
      content-class="pr-3 pt-3"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('settings.quiz_modal.button_start')"
      :title="quizName + ': '+ $i18n('settings.quiz_modal.title')"
      @ok="startQuiz"
    >
      <QuizStartPopup />
    </b-modal>
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
    quizState: { type: Number, default: 0 },
    failedCount: { type: Number, default: null },
    maxFailedCount: { type: Number, default: null },
  },
  data () {
    return {
      startWithTime: false,
    }
  },
  methods: {
    showModal (withTime) {
      this.startWithTime = withTime
      this.$refs.quizModal.show()
    },
    startQuiz () {
      if (this.startWithTime) {
        ajreq('startquiz', { app: 'quiz', easymode: 1, qid: this.quizId })
      } else {
        ajreq('startquiz', { app: 'quiz', qid: this.quizId })
      }
    },
  },
}
</script>

<style scoped>

</style>
