<template>
  <div class="bootstrap">
    <div class="page-container page-simple">
      <div class="row">
        <div class="col-4">
          <ConversationCard
            v-for="conversation in conversations"
            :key="conversation.id"
            :class="['border']"
            :title="conversation.title"
            :preview-message="conversation.lastMessage.body"
            @click="() => { openConversation(conversation.id) }"
          />
        </div>

        <div class="col-8">
          <ChatWindow
            :title="'Test123'"
            :class="['border']"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ConversationCard from '@/components/Message/LiteChat/ConversationCard.vue'
import { getConversationList } from '@/api/conversations'
import ChatWindow from '@/components/Message/LiteChat/ChatWindow.vue'

export default {
  name: 'LiteChat',
  components: {
    ChatWindow,
    ConversationCard,
  },
  data () {
    return {
      conversations: [],
      profilesWithNames: {},
    }
  },
  async mounted () {
    await this.loadConversations()
  },
  methods: {
    async loadConversations () {
      const conversationList = await getConversationList('20', '0')
      const profilesWithNames = {}

      conversationList.profiles.forEach(profile => {
        const name = profile.name
        const id = profile.id

        profilesWithNames[id] = name
      })

      conversationList.conversations.forEach(conversation => {
        conversation.memberNames = conversation.members.map(memberId => {
          return profilesWithNames[memberId]
        })

        conversation.title = conversation.title ?? conversation.memberNames.join(', ')
      })

      this.conversations = conversationList.conversations
      this.profilesWithNames = conversationList.profiles
    },
    openConversation (conservationId) {

    },
  },
}
</script>

<style scoped>

</style>
