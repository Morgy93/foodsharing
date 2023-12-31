<template>
  <div
    :id="`post-${id}`"
    class="thread"
  >
    <div
      class="card mb-2"
      :class="{'disabledLoading': isLoading}"
    >
      <div class="card-header d-flex align-items-center justify-content-between">
        <a
          class="d-flex align-items-center"
          :href="$url('profile', author.id)"
        >
          <Avatar
            v-if="wXS"
            :url="author.avatar"
            :is-sleeping="author.sleepStatus"
            :size="35"
            class="mr-2"
          />
          <strong class="author">
            {{ author.name }}
          </strong>
        </a>
        <ThreadPostDate
          v-if="wXS"
          :link="deepLink"
          :date="createdAt"
          @scroll="$emit('scroll', $event)"
        />
      </div>
      <div class="d-flex m-2">
        <div
          v-if="!wXS"
          class="mr-2 pr-2 border-right border-light text-center"
          style="min-width: 150px"
        >
          <a
            :href="$url('profile', author.id)"
            class="d-block"
          >
            <Avatar
              :url="author.avatar"
              :is-sleeping="author.sleepStatus"
              :size="130"
              class="mb-2"
              :auto-scale="false"
            />
          </a>
          <a
            v-if="!wXS && !isMe"
            class="btn btn-sm btn-outline-primary"
            @click="openChat"
          >
            <i class="fas fa-fw fa-comments" />
            {{ $i18n('chat.open_chat') }}
          </a>
        </div>
        <div
          class="body m-2 mr-md-5 text-break"
          v-html="body"
        />
      </div>
      <div class="card-footer">
        <div class="d-flex align-items-center justify-content-end justify-content-sm-between">
          <ThreadPostDate
            v-if="!wXS"
            :link="deepLink"
            :date="createdAt"
            classes="text-muted"
            @scroll="$emit('scroll', $event)"
          />
          <ThreadPostActions
            :reactions="reactions"
            :may-delete="mayReply && mayDelete"
            :may-edit="mayEdit"
            :may-reply="mayReply"
            @delete="$emit('delete')"
            @reaction-add="$emit('reaction-add', $event)"
            @reaction-remove="$emit('reaction-remove', $event)"
            @reply="$emit('reply', body)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Avatar from '@/components/Avatar'
import ThreadPostActions from './ThreadPostActions'
import ThreadPostDate from './ThreadPostDate'
import conversationStore from '@/stores/conversations'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

export default {
  components: { Avatar, ThreadPostActions, ThreadPostDate },
  mixins: [MediaQueryMixin],
  props: {
    id: { type: Number, default: null },
    userId: { type: Number, required: true },
    body: { type: String, default: '' },
    author: { type: Object, default: () => ({ avatar: null }) },
    createdAt: { type: Date, default: null },
    deepLink: { type: String, default: '' },
    reactions: { type: Object, default: () => ({}) },
    mayEdit: { type: Boolean, default: false },
    mayDelete: { type: Boolean, default: false },
    isLoading: { type: Boolean, default: true },
    mayReply: { type: Boolean, default: true },
  },
  computed: {
    isMe () {
      return this.userId === this.author.id
    },
  },
  methods: {
    openChat () {
      conversationStore.openChatWithUser(this.author.id)
    },
  },
}
</script>
