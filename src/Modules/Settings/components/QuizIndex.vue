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
                variant="secondary"
                class="pl-5 pr-5"
                :href="$url('quizFoodsaverWiki')"
                target="_blank"
              >
                {{ $i18n('settings.foodsaver_quiz.quiz_time_limit') }}
              </b-button>
            </b-col>
            <b-col>
              <div>
                <b-button
                  variant="secondary"
                  class="pl-5 pr-5"
                  :href="$url('quizFoodsaverWiki')"
                  target="_blank"
                >
                  {{ $i18n('settings.foodsaver_quiz.quiz_without_time_limit') }}
                </b-button>
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

export default {
  props: {
    quizId: { type: Number, default: null },
    quizName: { type: String, default: '' },
  },
}
</script>

<style scoped>

</style>
