<template>
  <div class="card mb-2">
    <div class="card-body">
      <a
        class="card-title text-decoration-none font-weight-bold text-truncate d-block"
      >
        {{ title }}
      </a>
      <p class="card-text text-truncate">
        {{ previewMessage }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConversationCard',
  props: {
    title: { type: String, default: 'Conversation without name' },
    previewMessage: { type: String, default: '...' },
    isUnread: { type: Boolean, default: false },
    conversationId: { type: Number, default: null },
  },
}
</script>

<style scoped>

</style>
