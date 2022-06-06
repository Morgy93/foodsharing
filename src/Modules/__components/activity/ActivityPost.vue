<template>
  <li class="list-group-item activity-item">
    <div class="d-flex align-items-center mb-2">
      <a
        v-if="fs_id && fs_name"
        :href="$url('profile', fs_id)"
        class="d-flex align-items-center"
      >
        <div class="img-thumbnail d-flex mr-1">
          <img
            :alt="fs_name"
            :src="icon"
            width="16"
            height="16"
          >
        </div>
        <span>{{ fs_name }}</span>
      </a>
      <span v-else-if="fs_id">
        {{ $i18n('dashboard.deleted_user') }}
      </span>
      <a
        v-else-if="sender_email"
        v-b-tooltip="sender_email.length > 25 ? sender_email : null"
        :href="dashboardContentLink"
        class="d-inline-block text-truncate"
        style="max-width: 125px;"
        v-html="sender_email"
      />
      <i
        v-if="type !== 'friendWall'"
        class="text-muted fas fa-angle-right mr-1 ml-1"
      />
      <a
        v-if="type !== 'friendWall'"
        v-b-tooltip="title.length > 100 ? title : null"
        :href="dashboardContentLink"
        class="d-inline-block text-truncate"
        v-html="title"
      />
    </div>

    <div class="d-flex mt-3 text-break">
      <div
        v-if="gallery.length > 0"
        class="img-thumbnail d-inline-block mr-3 mb-3 h-100"
        style="max-width: 100px;"
      >
        <a
          v-for="img in gallery"
          :key="img.thumb"
          :href="dashboardContentLink"
        >
          <img
            :alt="$i18n('upload.preview_image')"
            :src="img.thumb"
          >
        </a>
      </div>
      <Markdown
        :source="truncatedText"
      />
    </div>
    <button
      v-if="isTruncatable"
      class="btn btn-sm btn-link pl-0 mt-n3 mb-3"
      @click="isTruncatedText = !isTruncatedText"
    >
      {{ isTruncatedText ? $i18n('activitypost.showeverything') : $i18n('activitypost.less') }}
      <i
        :class="{ 'fa-rotate-180': !isTruncatedText }"
        class="fas fa-fw fa-angle-down"
      />
    </button>
    <div
      v-if="canQuickreply"
      class="d-flex w-100 justify-content-center"
    >
      <textarea
        v-if="!qrLoading"
        v-model="quickreplyValue"
        name="quickreply"
        class="form-control"
        :placeholder="$i18n('activitypost.write')"
        @keyup.enter="sendQuickreply"
      />
      <span
        v-else
        class="loader"
      >
        <i class="fas fa-spinner fa-spin" />
      </span>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-2">
      <small class="text-muted order-2 order-sm-1">
        <i class="far fa-fw fa-clock" />
        <span v-b-tooltip="$dateFormat(time, 'full-short')"> {{ $dateDistanceInWords(time) }} </span>
      </small>
      <p
        v-if="source"
        v-b-tooltip="source.length > 40 ? source : null"
        class="text-truncate order-1 order-sm-2 mb-0"
        v-html="$i18n(translationKey, [source])"
      />
    </div>
  </li>
</template>

<script>
import serverData from '@/server-data'
import { sendQuickreply } from '@/api/dashboard'
import { pulseInfo } from '@/script'
import { url } from '@/urls'
import Markdown from '@/components/Markdown/Markdown'
import { createPost } from '@/api/forum'

export default {
  components: { Markdown },
  filters: {
    truncate: function (text, maxLength, clamp) {
      clamp = clamp || '...'
      return text.length > maxLength ? text.slice(0, maxLength) + clamp : text
    },
  },
  /* eslint-disable vue/prop-name-casing */
  props: {
    // Shared properties
    time: { type: Date, required: true },

    type: { type: String, required: true },
    desc: { type: String, default: '' },
    title: { type: String, default: '' },

    icon: { type: String, default: '' },
    source: { type: String, default: '' },
    source_suffix: { type: String, default: '' },
    gallery: { type: Array, default: () => { return [] } },
    quickreply: { type: String, default: '' },

    fs_id: { type: Number, default: null },
    fs_name: { type: String, default: '' },
    region_id: { type: Number, default: null },
    entity_id: { type: Number, default: null },

    // Individual update-type properties for forum posts: ActivityUpdateForum
    forum_post: { type: Number, default: null },
    forum_type: { type: String, default: '' },

    // Individual update-type properties for mailboxes: ActivityUpdateMailbox
    sender_email: { type: String, default: '' },
  },
  /* eslint-enable */
  data () {
    return {
      isTruncatedText: true,
      qrLoading: false,
      user_id: serverData.user.id,
      user_avatar: serverData.user.avatar.mini,
      quickreplyValue: null,
    }
  },
  computed: {
    dashboardContentLink () {
      switch (this.type) {
        case 'event':
          return url('event', this.entity_id)
        case 'foodsharepoint':
          return url('foodsharepoint', this.region_id, this.entity_id)
        case 'friendWall':
          return url('profile', this.fs_id)
        case 'forum':
          return url('forum', this.region_id, (this.forum_type === 'botforum'), this.entity_id, this.forum_post)
        case 'mailbox':
          return url('mailbox', this.entity_id)
        case 'store':
          return url('store', this.entity_id)
        default:
          return '#'
      }
    },
    isTruncatable () {
      return this.desc.split(' ').length > 30
    },
    truncatedText () {
      if (this.isTruncatable && this.isTruncatedText) {
        return this.desc.split(' ').splice(0, 30).join(' ') + ' ...'
      } else {
        return this.desc
      }
    },
    translationKey () {
      return 'dashboard.source_' + this.type + this.source_suffix
    },
    canQuickreply () {
      // old endpoints use the 'quickreply' variable, new endpoints are distinguishable by the activity's type
      return (this.quickreply !== null && this.quickreply.length > 0) || this.type === 'forum'
    },
  },
  methods: {
    async sendQuickreply () {
      if (this.quickreplyValue !== null && this.quickreplyValue.trim().length !== 0) {
        console.log('sending reply', this.quickreplyValue)
        this.qrLoading = true

        try {
          if (this.type === 'forum') {
            // forum posts already use the REST API for quickreplies
            await createPost(this.entity_id, this.quickreplyValue)
            pulseInfo(this.$i18n('forum.quickreply.success'))
          } else {
            // quickreplies to emails and wall posts (events, buddies, and stores) still use old XHR requests
            const { message } = await sendQuickreply(this.quickreply, this.quickreplyValue)
            pulseInfo(message)
          }
          this.quickreplyValue = ''
        } catch (e) {
          pulseInfo(this.$i18n('forum.quickreply.error'))
        } finally {
          this.qrLoading = false
        }
      }
      return true
    },
  },
}
</script>

<style lang="scss" scoped>
.activity-item:first-child {
  border-bottom-right-radius: var(--border-radius);
  border-bottom-left-radius: var(--border-radius);
  margin-bottom: .5rem;
}

.activity-item:not(:first-child) {
  border-radius: var(--border-radius);
  margin-bottom: .5rem;
}

.activity-item .markdown p:last-child {
  margin-bottom: 0;
}
</style>
