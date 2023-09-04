<template>
  <Container
    :title="$i18n('quiz.plural')"
  >
    <a
      v-for="quiz in visibleQuizes"
      :key="quiz.id"
      type="button"
      class="list-group-item list-group-item-action"
      :href="`/quiz/edit/${quiz.id}`"
    >
      <i
        v-b-tooltip="$i18n('quiz.editable_tooltip')"
        class="fas fa-fw fa-cog"
        :class="quiz.edit ? '' : 'invisible'"
      />
      {{ quiz.name }}
    </a>
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
      type: Array,
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
