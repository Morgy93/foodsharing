<template>
  <Container
    :title="'Quizze'"
  >
    <a
      v-for="quiz in visibleQuizes"
      :key="quiz.id"
      type="button"
      class="list-group-item list-group-item-action"
      :href="`/quiz/edit/${quiz.id}`"
      v-text="quiz.name"
    />
  </Container>
</template>

<script>
import Container from '@/components/Container/Container.vue'

export default {
  components: {
    Container,
  },
  props: {
    visibleQuizes: {
      type: Object,
      required: true,
    },
  },
  methods: { },
}
</script>
<style lang="scss" scoped>
.list-group-item:not(:last-child) {
  border-bottom: 0;
}
</style>
