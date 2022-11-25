<template>
  <div id="header">
    <span v-if="conversationId===null">{{ $i18n('chat.new_message') }}</span>
    <a
      v-else-if="storeId"
      id="title"
      :href="$url('store', storeId)"
    >
      {{ title }}
    </a>
    <span
      v-else
      id="title"
    >
      {{ title }}
    </span>

    <div class="images">
      <a
        v-for="member in members"
        :key="member.id"
        class="member-img"
        :title="member.name"
        :href="$url('profile', member.id)"
      >
        <Avatar
          :url="member.avatar"
          :size="24"
          :auto-scale="false"
        />
      </a>
    </div>
  </div>
</template>

<script>
import Avatar from '@/components/Avatar.vue'

// Stores
import conversationStore from '@/stores/conversations'
import ProfileStore from '@/stores/profiles'
import DataUser from '@/stores/user'

const LIMIT_DISPLAYED_USERS = 35

export default {
  components: {
    Avatar,
  },
  props: {
    conversationId: {
      type: Number,
      default: null,
    },
  },
  data () {
    return {
      currentUserId: DataUser.getters.getUserId(),
      title: '',
      storeId: null,
      members: [],
    }
  },
  computed: {
  },
  watch: {
    async conversationId (newConversationId, oldConversationId) {
      await this.init()
    },
  },
  async created () {
    await this.init()
  },
  methods: {
    async init () {
      if (this.conversationId === null) {
        this.title = ''
        this.storeId = null
        this.members = []
        return
      }
      const conversation = await conversationStore.getConversation(this.conversationId)
      const otherMembers = conversation.members.filter(m => m !== this.currentUserId).slice(0, LIMIT_DISPLAYED_USERS)

      const title = conversation.title || `${otherMembers.map(member => ProfileStore.profiles[member].name).join(', ')}`

      const members = otherMembers.map(member => (
        {
          id: ProfileStore.profiles[member].id,
          name: ProfileStore.profiles[member].name,
          avatar: ProfileStore.profiles[member].avatar,
        }
      ),
      )

      this.storeId = conversation.storeId
      this.title = title
      this.members = members
    },
  },
}
</script>

<style lang="scss" scoped>

#header {
  display: flex;
  align-items: center;
}

#title {
  margin-right: 10px;
}

.member-img {
  padding-left: 2px;
}

.avatar {
  vertical-align: middle;
}

#title {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
}

.images {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
}

</style>
