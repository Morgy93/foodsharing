<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <Container
    :title="title"
    :collapsible="false"
    :wrap-content="true"
  >
    <p>{{ quiz.desc }}</p>

    <div v-if="quizStatus.status === 0">
      <b-button
        block
        variant="primary"
        @click="showStartModal(true)"
      >
        {{ $i18n('quiz.timedstart') }}
      </b-button>
      <b-button
        block
        variant="primary"
        @click="showStartModal(false)"
      >
        {{ $i18n('quiz.regstart') }}
      </b-button>
    </div>

    {{ quizStatus }}
    <b-modal
      ref="start-info-modal"
      :title="$i18n('quiz.startmodal.title')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.start')"
      centered
      size="lg"
      @ok="startQuiz"
    >
      <ul>
        <li
          v-for="infoKey in infoKeys"
          :key="infoKey"
        >
          {{ $i18n(`quiz.startmodal.infos.${infoKey}`) }}
        </li>
      </ul>
    </b-modal>
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
      type: Number,
      default: -1,
    },
  },
  data () {
    return {
      infoKeys: ['wiki', 'real_life_examples', 'limited_tries', 'alone', 'read_carefully', 'multiple_choice', 'comment', 'pause', 'feedback'],
      timed: undefined,
    }
  },
  computed: {
    title () {
      return i18n(`quiz.title.${this.quizStatus.status}`, { name: this.quiz.name })
    },
  },
  methods: {
    showStartModal (timed) {
      this.timed = timed
      this.$refs['start-info-modal'].show()
    },
    startQuiz () {

    },
  },
}
</script>

<style>

</style>
