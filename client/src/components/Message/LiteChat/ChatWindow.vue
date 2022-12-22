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
        v-for="message in messages"
        :key="message.id"
        :author-name="profilesWithNames[message.authorId]"
        :author-id="message.authorId"
        :message="message.body"
        :sent-at="message.sentAt"
      />
    </div>
    <div class="card-footer">
      <textarea
        v-model="newMessage"
        class="form-control mb-1"
      />
      <button
        class="btn btn-primary btn-sm btn-block"
        type="button"
        :disabled="isMessageEmpty"
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
    conversationId: { type: Number, default: null },
    messages: { type: Array, required: true },
    profilesWithNames: { type: Object, required: true },
  },
  data () {
    return {
      newMessage: '',
    }
  },
  computed: {
    isMessageEmpty () {
      return this.newMessage === ''
    },
  },
}
</script>

<style scoped>
</style>
