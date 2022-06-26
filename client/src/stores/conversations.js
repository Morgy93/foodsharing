import Vue from 'vue'
import { getConversationList, getConversation, getMessages, markConversationRead } from '@/api/conversations'
import ProfileStore from '@/stores/profiles'
import serverData from '@/server-data'

export const store = Vue.observable({
  conversations: [],
})

export const getters = {
  get () {
    return store.conversations.length > 0 ? store.conversations : []
  },

  getSorted () {
    const messages = getters.get().filter((a) => (a.lastMessage != null))
    return messages.sort((a, b) => {
      if (a.hasUnreadMessages === b.hasUnreadMessages) {
        return a.lastMessage.sentAt < b.lastMessage.sentAt ? 1 : -1
      } else {
        return a.hasUnreadMessages ? -1 : 1
      }
    })
  },

  getUnreadCount () {
    const count = store.conversations.length
    if (count > 0) {
      return count < 99 ? count : '99+'
    }
    return null
  },

}

export const mutations = {
  async fetchConversations (limit = 20) {
    const res = await getConversationList(limit)
    ProfileStore.updateFrom(res.profiles)
    for (const conversation of res.conversations) {
      const c = this.conversations[conversation.id] ?? { messages: {} }
      Vue.set(this.conversations, conversation.id, {
        ...conversation,
        messages: Object.assign({}, c.messages),
        lastMessage: convertMessage(conversation.lastMessage),
      })
    }
  },
  async fetchConversation (id) {
    const c = this.conversations[id] ?? { messages: {} }
    /* always load conversation for proper read mark handling.
    * Will still cache messages during store lifetime */
    const res = await getConversation(id)
    ProfileStore.updateFrom(res.profiles)
    for (const message of res.conversation.messages) {
      c.messages[message.id] = convertMessage(message)
    }
    Vue.set(this.conversations, id, {
      ...res.conversation,
      messages: c.messages,
      lastMessage: convertMessage(res.conversation.lastMessage),
    })
  },

  async loadMoreMessages (cid) {
    const c = this.conversations[cid]
    const res = await getMessages(cid, Object.keys(c.messages)[0])
    ProfileStore.updateFrom(res.profiles)
    for (const message of res.messages) {
      c.messages[message.id] = convertMessage(message)
    }
    return res.messages.length
  },

  async updateFromPush (data) {
    const cid = data.cid
    if (!(cid in this.conversations)) {
      await this.loadConversation(cid)
      /* likely, when loading the conversation after the push message appeared, we don't need to add the push message.
      Still, I think it shouldn't harm...
       */
    }
    const message = data.message
    Vue.set(this.conversations[cid].messages, message.id, message)
    Vue.set(this.conversations[cid], 'lastMessage', message)
    if (message.authorId !== serverData.user.id) {
      Vue.set(this.conversations[cid], 'hasUnreadMessages', true)
    }
  },
  async markAsRead (cid) {
    if (cid in this.conversations && this.conversations[cid].hasUnreadMessages) {
      Vue.set(this.conversations[cid], 'hasUnreadMessages', false)
      await markConversationRead(cid)
    }
  },
  async markUnreadMessagesAsRead () {
    for (const cid in this.conversations) {
      await this.markAsRead(cid)
    }
  },
}

export function convertMessage (val) {
  if (val !== null) {
    return {
      ...val,
      sentAt: new Date(val.sentAt),
    }
  } else {
    return null
  }
}

export default { store, getters, mutations }
