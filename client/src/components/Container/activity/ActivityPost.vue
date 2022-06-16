<template>
  <li
    class="list-group-item activity-item"
    :class="{
      'list-group-item-action clickable': canQuickreply || isTruncatable,
    }"
    @click.stop="toggleState"
  >
    <div class="d-flex align-items-center mb-2 font-weight-bold">
      <a
        v-if="fs_id && fs_name"
        :href="$url('profile', fs_id)"
        class="d-flex align-items-center"
      >
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

    <div class="d-flex mb-2 text-break">
      <a
        v-b-tooltip="fs_id ? fs_name : sender_email"
        :href="fs_id ? $url('profile', fs_id) : dashboardContentLink"
        class="icon w-20 mr-2 d-flex text-center justifiy-content-center align-items-center align-self-start"
      >
        <Avatar
          v-if="fs_id"
          class="img-thumbnail"
          :url="icon"
          :size="50"
        />
        <i
          v-else
          class="d-flex text-secondary img-thumbnail w-100 h-100 align-items-center justify-content-center"
          style="min-width: 50px;"
          :class="[icon]"
        />
        <span
          class="hide-for-users"
          v-html="fs_id ? fs_name : sender_email"
        />
      </a>
      <div class="content">
        <div
          v-if="gallery.length > 0"
          class="d-inline-flex mb-3"
        >
          <a
            v-for="img in gallery.slice(0,4)"
            :key="img.thumb"
            :href="dashboardContentLink"
            class="img-thumbnail mr-1"
          >
            <img
              :alt="$i18n('upload.preview_image')"
              :src="img.thumb"
              loading="lazy"
            >
          </a>
        </div>
        <Markdown
          :source="!state ? truncate(desc, truncatedLength) : desc"
        />
        <button
          v-if="isTruncatable || canQuickreply"
          class="btn btn-sm btn-link pl-0 mt-n3 mb-1"
          @click.stop.prevent="toggleState"
        >
          <span
            v-if="isTruncatable"
            v-html="!state ? $i18n('dashboard.showmore') : $i18n('dashboard.showless')"
          />
          <span
            v-if="isTruncatable && canQuickreply"
            v-html="!state ? '&' : ''"
          />
          <span
            v-if="canQuickreply && isTruncatable"
            v-html="!state ? $i18n('activitypost.response') : ''"
          />
          <span
            v-if="canQuickreply && !isTruncatable"
            v-html="!state ? $i18n('activitypost.Response') : $i18n('dashboard.showless')"
          />
          <i
            :class="{ 'fa-rotate-180': state }"
            class="fas fa-fw fa-angle-down"
          />
        </button>
      </div>
    </div>
    <div
      v-if="canQuickreply && state"
      class="d-flex w-100 flex-column justify-content-center"
    >
      <div
        v-if="!qrLoading"
      >
        <div class="position-relative">
          <textarea
            ref="quickreply"
            v-model="quickreplyValue"
            name="quickreply"
            class="form-control"
            :placeholder="$i18n('activitypost.write')"
            rows="1"
            @click.stop="$refs.quickreply.focus()"
            @keyup="resizeTextarea"
            @keydown.enter.exact.prevent="send()"
          />
          <button
            v-b-tooltip.html="$i18n('activitypost.quickreply_button')"
            class="btn mt-2 btn-primary"
            :class="{
              'position-absolute btn-sm': !viewIsMobile,
              'btn-block': viewIsMobile,
              'btn-outline-primary': !quickreplyValue
            }"
            style="bottom:1rem; right:1rem;"
            :disabled="!quickreplyValue"
            @click="send(true)"
          >
            <i class="fas fa-paper-plane" />
            <span
              v-if="viewIsMobile"
              v-html="$i18n('activitypost.Response')"
            />
          </button>
        </div>
        <small
          v-if="!viewIsMobile"
          class="d-inline-block mt-2 text-muted"
        >
          <i class="fas fa-info-circle" />
          <span v-html="$i18n('activitypost.quickreply_info')" />
        </small>
      </div>
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
        <span v-b-tooltip="dateFormat(time, 'full-short')"> {{ dateDistanceInWords(time) }} </span>
      </small>
      <small
        v-if="source"
        v-b-tooltip="source.length > 40 ? source : null"
        class="text-truncate order-1 order-sm-2 mb-0"
        v-html="$i18n(translationKey, [source])"
      />
    </div>
  </li>
</template>

<script>
import DateFormatterMixin from '@/mixins/DateFormatterMixin'
import TruncateMixin from '@/mixins/TruncateMixin'
import StateTogglerMixin from '@/mixins/StateTogglerMixin'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import AutoResizeTextareaMixin from '@/mixins/AutoResizeTextareaMixin'

import serverData from '@/server-data'
import { sendQuickreply } from '@/api/dashboard'
import { pulseInfo } from '@/script'
import { url } from '@/urls'
import { createPost } from '@/api/forum'

import Markdown from '@/components/Markdown/Markdown'
import Avatar from '@/components/Avatar'

export default {
  components: { Markdown, Avatar },
  mixins: [DateFormatterMixin, TruncateMixin, StateTogglerMixin, MediaQueryMixin, AutoResizeTextareaMixin],
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
      truncatedLength: 280,
      isTruncatedText: true,
      qrLoading: false,
      user_id: serverData.user.id,
      user_avatar: serverData.user.avatar.mini,
      quickreplyValue: '',
    }
  },
  computed: {
    dashboardContentLink () {
      switch (this.type) {
        case 'event':
          return url('event', this.entity_id)
        case 'foodsharepoint':
          return url('foodsharepoint', this.entity_id)
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
      return this.desc.length > this.truncatedLength
    },
    translationKey () {
      return 'dashboard.source_' + this.type + this.source_suffix
    },
    canQuickreply () {
      // old endpoints use the 'quickreply' variable, new endpoints are distinguishable by the activity's type
      return (this.quickreply !== null && this.quickreply.length > 0) || this.type === 'forum'
    },
    isReplyEmpty () {
      return (
        this.quickreplyValue.trim() === null ||
        this.quickreplyValue.trim().length === 0
      )
    },
  },
  methods: {
    newLine () {
      this.quickreplyValue += '\n'
    },
    async send (forced = false) {
      if ((this.viewIsMD && !this.isReplyEmpty) || (forced && !this.isReplyEmpty)) {
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
      } else {
        this.newLine()
      }
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

.activity-item .markdown a {
  text-decoration: underline;
}

.activity-item .markdown p:last-child {
  margin-bottom: 0;
}

.clickable {
  cursor: pointer;
}

.form-control {
  min-height: 6rem;
  overflow: hidden;
  cursor: text;
}

.icon {
  height: 50px;
  width: 50px;
  line-height: 0.7em;
  font-size: 1.5rem;

  &:hover {
    text-decoration: none;

    & .img-thumbnail {
      background-color: var(--light);
    }
  }
}

::v-deep.markdown p {
  font-size: 15px;
  color: var(--dark);
  line-height: 1.5;
}

::v-deep.markdown a[href]{
 font-weight: bold;
//  text-decoration: underline;
}
</style>
