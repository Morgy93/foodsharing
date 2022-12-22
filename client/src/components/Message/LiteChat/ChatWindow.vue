<template>
  <div class="card">
    <h3 class="card-header">
      {{ title }}
    </h3>
    <div class="card-body">
      <button class="btn btn-sm btn-outline-primary btn-block">
        Ältere Nachrichten laden
      </button>
      <hr>
      <Message
        :author-name="'Martin'"
        :author-id="1"
        :message="'daddasdad'"
        :sent-at="new Date()"
      />
    </div>
    <div class="card-footer">
      <textarea
        class="form-control mb-1"
      />
      <button
        class="btn btn-primary btn-sm btn-block"
        type="button"
      >
        Senden
      </button>
    </div>
  </div>
</template>

<script>
import Message from '@/components/Message/LiteChat/Message.vue'

export default {
  name: 'ChatWindow',
  components: { Message },
  props: {
    title: { type: String, required: true },
    conversationId: { type: String, default: null },
  },
}
</script>

<style scoped>
</style>
