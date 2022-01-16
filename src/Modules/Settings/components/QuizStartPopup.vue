<template>
  <div>
    <p>
      <i class="fas fa-glasses" /> {{ $i18n('settings.quiz_modal.before_title') }}
    </p>
    <ul class="mb-4">
      <li>{{ $i18n('settings.quiz_modal.before_text_1') }}</li>
      <li>{{ $i18n('settings.quiz_modal.before_text_2') }}</li>
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
  </div>
</template>

<script>
export default {
  name: 'QuizStartPopup',
}
</script>

<style scoped>

</style>
