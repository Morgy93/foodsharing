<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <Container
    :title="title"
    :collapsible="false"
    :wrap-content="true"
  >
    <p>{{ quiz.desc }}</p>

    <b-button block variant="primary">
      {{ $i18n('quiz.timedstart') }}
    </b-button>
    <b-button block variant="primary">
      {{ $i18n('quiz.regstart') }}
    </b-button>
    {{ quizStatus }}
  </Container>
</template>

<script>

import Container from '@/components/Container/Container.vue'
import i18n from '@/helper/i18n'

export default {
  components: {
    Container,
  },
  props: {
    ownId: {
      type: Number,
      default: -1,
    },
    quizStatus: {
      type: Object,
      default: () => null,
    },
    quiz: {
      type: Object,
      default: () => null,
    },
    role: {
      type: Object,
      default: () => null,
    },
  },
  computed: {
    title () {
      return i18n('quiz.title_start', { name: this.quiz.name })
    },
  },
}
</script>

<style>

</style>
