<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <div
    :class="{disabledLoading: isLoading}"
    class="bootstrap"
  >
    <div v-if="isLoading && !regionId">
      <div class="card-header text-white bg-primary">
        {{ title || '...' }}
      </div>
      <div class="card-body p-5" />
    </div>

    <div
      v-if="regionId"
      class="card rounded above"
    >
      <div class="card-header text-white bg-primary">
        <div class="row m-1">
          <h4 class="text-truncate">
            <i v-if="!isOpen" class="fas fa-lock mr-1" :title="$i18n('forum.thread.closed')" />
            {{ title }}
          </h4>
        </div>
      </div>
      <ThreadActions
        :is-following-bell.sync="isFollowingBell"
        :is-following-email.sync="isFollowingEmail"
        :is-sticky.sync="isSticky"
        :may-moderate="mayModerate"
        :status="status"
        @update:follow-bell="updateFollowBell"
        @update:follow-email="updateFollowEmail"
        @update:sticky="updateStickyness"
        @close="close"
        @open="open"
      />
      <div
        v-if="!isActive && mayModerate"
        class="card-body mb-2"
      >
        <div
          class="alert alert-warning mb-2"
          role="alert"
        >
          <span>
            {{ $i18n('forum.thread.inactive') }}
          </span>
        </div>
        <div>
          <button
            class="btn btn-primary btn-sm"
            @click="activateThread"
          >
            <i class="fas fa-check" /> {{ $i18n('forum.thread.activate') }}
          </button>
          <button
            class="btn btn-danger btn-sm float-right"
            @click="$refs.deleteModal.show()"
          >
            <i class="fas fa-trash-alt" /> {{ $i18n('forum.thread.delete') }}
          </button>
        </div>
      </div>
    </div>

    <ThreadFastnavigationButton v-if="isFastNavigationVisible" :label="$i18n('forum.thread.navigate_to_newest_post')" @navigate="navigateToNewestPost" />

    <div
      v-for="post in posts"
      :key="post.id"
    >
      <ThreadPost
        :id="post.id"
        :user-id="userId"
        :author="post.author"
        :body="post.body"
        :deep-link="getPostLink(post.id)"
        :reactions="post.reactions"
        :may-delete="post.mayDelete"
        :may-edit="false"
        :is-loading="loadingPosts.indexOf(post.id) != -1"
        :created-at="new Date(post.createdAt)"
        :may-reply="isOpen"
        @delete="deletePost(post)"
        @reaction-add="reactionAdd(post, arguments[0])"
        @reaction-remove="reactionRemove(post, arguments[0])"
        @reply="reply"
        @scroll="scrollToPost(post.id)"
      />
    </div>

    <ThreadFastnavigationButton v-if="isFastNavigationVisible" :label="$i18n('forum.thread.navigate_to_oldest_post')" @navigate="navigateToOldestPost" />

    <div
      v-if="regionId"
      class="card rounded below"
    >
      <ThreadActions
        :is-following-bell="isFollowingBell"
        :is-following-email="isFollowingEmail"
        :is-sticky="isSticky"
        :may-moderate="mayModerate"
        :status="status"
        @update:follow-bell="updateFollowBell"
        @update:follow-email="updateFollowEmail"
        @update:sticky="updateStickyness"
        @close="close"
        @open="open"
      />
    </div>

    <div
      v-if="!isLoading && !errorMessage && !posts.length"
      class="alert alert-warning"
      role="alert"
    >
      {{ $i18n('forum.no_posts') }}
    </div>
    <div
      v-if="errorMessage"
      class="alert alert-danger"
      role="alert"
    >
      <strong>{{ $i18n('error_unexpected') }}:</strong> {{ errorMessage }}
    </div>
    <ThreadForm
      v-if="isOpen"
      ref="form"
      :error-message="errorMessage"
      @submit="createPost"
    />

    <b-modal
      ref="deleteModal"
      :title="$i18n('forum.thread.delete')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      cancel-variant="primary"
      ok-variant="outline-danger"
      @ok="deleteThread"
    >
      {{ $i18n('really_delete') }}
    </b-modal>
  </div>
</template>

<script>

import { BModal } from 'bootstrap-vue'

import ThreadActions from './ThreadActions'
import ThreadForm from './ThreadForm'
import ThreadPost from './ThreadPost'
import ThreadFastnavigationButton from './ThreadFastnavigationButton'
import * as api from '@/api/forum'
import { pulseError } from '@/script'
import i18n from '@/helper/i18n'
import DataUser from '@/stores/user'
import { GET } from '@/browser'
import { setThreadStatus } from '@/api/forum'
import ThreadStatus from './ThreadStatus'

export default {
  components: { BModal, ThreadActions, ThreadForm, ThreadPost, ThreadFastnavigationButton },
  props: {
    id: {
      type: Number,
      default: null,
    },
  },
  data () {
    return {
      title: '',
      regionId: null,
      regionSubId: null,
      posts: [],

      isSticky: true,
      isActive: true,
      mayModerate: false,
      mayDelete: false,
      isFollowingEmail: true,
      isFollowingBell: true,

      isLoading: false,
      loadingPosts: [],
      errorMessage: null,

      status: ThreadStatus.THREAD_OPEN,
    }
  },
  computed: {
    userId () {
      return DataUser.getters.getUserId()
    },
    userFirstName () {
      return DataUser.getters.getUserFirstName()
    },
    isOpen () {
      return this.status === ThreadStatus.THREAD_OPEN
    },
    newestPostId () {
      return this.posts[this.posts.length - 1].id
    },
    oldestPostId () {
      return this.posts[0].id
    },
    isFastNavigationVisible () {
      return this.posts.length > 2
    },
  },
  async created () {
    this.isLoading = true
    await this.reload()
    setTimeout(() => { this.scrollToPost(GET('pid')) }, 200)
  },
  methods: {
    getPostLink (postId) {
      return this.$url('forum', this.regionId, this.regionSubId, this.id, postId)
    },
    scrollToPost (postId) {
      const deepLink = this.getPostLink(postId)
      window.history.pushState({ postId }, 'Post deeplink', deepLink)

      const p = window.document.getElementById(`post-${postId}`)
      if (p) {
        p.scrollIntoView({ behavior: 'smooth', block: 'center' })
      }
    },
    navigateToNewestPost () {
      this.scrollToPost(this.newestPostId)
    },
    navigateToOldestPost () {
      this.scrollToPost(this.oldestPostId)
    },
    reply (body) {
      // this.$refs.form.text = `> ${body.split('\n').join('\n> ')}\n\n${this.$refs.form.text}`
      this.$refs.form.focus()
    },
    async reload (isDeleteAction = false) {
      try {
        const res = (await api.getThread(this.id)).data
        Object.assign(this, {
          title: res.title,
          regionId: res.regionId,
          regionSubId: res.regionSubId,
          posts: res.posts,
          isSticky: res.isSticky,
          isActive: res.isActive,
          mayModerate: res.mayModerate,
          mayDelete: res.mayDelete,
          isFollowingEmail: res.isFollowingEmail,
          isFollowingBell: res.isFollowingBell,
          status: res.status,
        })
        this.isLoading = false
      } catch (err) {
        if (!isDeleteAction) {
          this.isLoading = false
          this.errorMessage = err.message
        } else {
          // In this case the last post was deleted.
          window.location = this.$url('forum', this.regionId)
        }
      }
    },
    async updateFollowBell () {
      const targetState = !this.isFollowingBell
      try {
        if (targetState) {
          await api.followThreadByBell(this.id)
        } else {
          await api.unfollowThreadByBell(this.id)
        }
        this.isFollowingBell = targetState
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      }
    },
    async updateFollowEmail () {
      const targetState = !this.isFollowingEmail
      try {
        if (targetState) {
          await api.followThreadByEmail(this.id)
        } else {
          await api.unfollowThreadByEmail(this.id)
        }
        this.isFollowingEmail = targetState
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      }
    },
    async updateStickyness () {
      const targetState = !this.isSticky
      try {
        if (targetState) {
          await api.stickThread(this.id)
        } else {
          await api.unstickThread(this.id)
        }
        this.isSticky = targetState
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      }
    },
    async deletePost (post) {
      this.loadingPosts.push(post.id)

      try {
        await api.deletePost(post.id)
        await this.reload(true)
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      } finally {
        this.loadingPosts.splice(this.loadingPosts.indexOf(post.id), 1)
      }
    },

    async reactionAdd (post, key, onlyLocally = false) {
      if (post.reactions[key]) {
        // reaction alrready in list, increase count by 1
        if (post.reactions[key].find(r => r.id === this.userId)) return // already given - abort
        post.reactions[key].push({ id: this.userId, name: this.userName })
      } else {
        // reaction not in the list yet, append it
        this.$set(post.reactions, key, [{ id: this.userId, name: this.userName }])
      }

      if (!onlyLocally) {
        try {
          await api.addReaction(post.id, key)
        } catch (err) {
          // failed? remove it again
          this.reactionRemove(post, key, true)
          pulseError(i18n('error_unexpected'))
        }
      }
    },
    async reactionRemove (post, key, onlyLocally = false) {
      const reactionUser = post.reactions[key].find(r => r.id === this.userId)

      if (!reactionUser) return

      post.reactions[key].splice(post.reactions[key].indexOf(reactionUser), 1)

      if (!onlyLocally) {
        try {
          await api.removeReaction(post.id, key)
        } catch (err) {
          // failed? add it again
          this.reactionAdd(post, key, true)
          pulseError(i18n('error_unexpected'))
        }
      }
    },
    async createPost (body) {
      this.errorMessage = null
      const dummyPost = {
        id: -1,
        createdAt: new Date(),
        body: body,
        reactions: {},
        author: {
          name: `${this.userFirstName} ${DataUser.getters.getUserLastName()}`,
          avatar: DataUser.getters.getAvatar(),
        },
      }
      this.loadingPosts.push(-1)
      this.posts.push(dummyPost)

      try {
        await api.createPost(this.id, body)
        await api.followThreadByBell(this.id)
        await this.reload()
      } catch (err) {
        const index = this.posts.indexOf(dummyPost)
        this.posts.splice(index, 1)

        this.errorMessage = err.message
        this.$refs.form.text = body
      }
    },

    async activateThread () {
      this.isActive = true
      try {
        await api.activateThread(this.id)
      } catch (err) {
        this.isActive = false
        pulseError(i18n('error_unexpected'))
      }
    },
    async deleteThread () {
      this.isLoading = true
      try {
        await api.deleteThread(this.id)

        // redirect to forum overview
        window.location = this.$url('forum', this.regionId, this.regionSubId)
      } catch (err) {
        this.isLoading = false
        pulseError(i18n('error_unexpected'))
      }
    },
    async close () {
      await this.setStatus(ThreadStatus.THREAD_CLOSED)
    },
    async open () {
      await this.setStatus(ThreadStatus.THREAD_OPEN)
    },
    async setStatus (status) {
      this.isLoading = true
      try {
        await setThreadStatus(this.id, status)
        this.status = status
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      }
      this.isLoading = false
    },
  },
}
</script>

<style lang="scss" scoped>
.card-body > .alert {
  margin-bottom: 0;
}
</style>
