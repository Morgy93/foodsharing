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
            @click.native="() => { openConversation(conversation.id, conversation.title) }"
          />
        </div>

        <div class="col-8">
          <ChatWindow
            v-if="chatWindow.isVisible"
            :title="chatWindow.conversation.title"
            :conversation-id="chatWindow.conversation.id"
            :messages="chatWindow.conversation.messages"
            :profiles-with-names="profilesWithNames"
            :class="['border']"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ConversationCard from '@/components/Message/LiteChat/ConversationCard.vue'
import { getConversationList, getMessages } from '@/api/conversations'
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
      chatWindow: { isVisible: false, messages: [], conversation: { id: null, title: null, messages: [] } },
    }
  },
  async mounted () {
    await this.loadConversations()
  },
  methods: {
    async loadConversations () {
      const conversationList = await getConversationList('50', '0')
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
    async openConversation (conversationId, conversationTitle) {
      this.chatWindow.conversation.id = conversationId
      this.chatWindow.conversation.title = conversationTitle

      const messagesAndProfiles = await getMessages(conversationId, null, '5')

      this.chatWindow.conversation.messages = messagesAndProfiles.messages ?? []
      messagesAndProfiles.profiles.forEach(profile => {
        const name = profile.name
        const id = profile.id

        this.profilesWithNames[id] = name
      })

      console.log(messagesAndProfiles)

      this.chatWindow.isVisible = true
    },
  },
}
</script>

<style scoped>

</style>
