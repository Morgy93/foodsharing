<template>
  <div class="store-wall">
    <div
      v-if="mayWritePost"
      class="newpost m-1 p-1"
    >
      <b-form-textarea
        id="newpost"
        v-model="newPostText"
        :placeholder="$i18n('wall.message_placeholder')"
        rows="2"
        max-rows="6"
        @keydown.ctrl.enter="writePost"
      />

      <div class="submit d-flex">
        <b-button
          class="ml-auto mt-2"
          :class="{'d-none': !newPostExists}"
          variant="outline-secondary"
          :disabled="!newPostExists"
          @click.prevent.stop="writePost"
        >
          {{ $i18n('button.send') }}
        </b-button>
      </div>
    </div>

    <ul
      v-if="displayedPosts"
      class="posts list-unstyled"
      :class="{'has-more-posts': hasMorePosts}"
    >
      <WallPost
        v-for="p in displayedPosts"
        :key="p.id"
        :post="p"
        :managers="managers"
        :may-delete-everything="mayDeleteEverything"
        class="wallpost"
        @delete-post="deletePost"
      />
    </ul>
    <b-link
      v-if="hasMorePosts"
      class="d-block text-muted text-center py-2"
      @click="isExcerptListExpanded = true"
    >
      {{ $i18n('wall.see-more') }}
    </b-link>
  </div>
</template>

<script>
import { getStoreWall, deleteStorePost, writeStorePost } from '@/api/stores'
import WallPost from '../../WallPost/components/WallPost'
import { showLoader, hideLoader, pulseError } from '@/script'

export default {
  components: { WallPost },
  props: {
    storeId: { type: Number, required: true },
    showOnlyExcerpt: { type: Boolean, default: false },
    managers: { type: Array, default: () => [] },
    mayWritePost: { type: Boolean, required: true },
    mayDeleteEverything: { type: Boolean, required: true },
    numberOfVisiblePostsPerExcerptIteration: { type: Number, default: 3 },
  },
  data () {
    return {
      posts: undefined,
      newPostText: '',
      isExcerptListExpanded: false,
    }
  },
  computed: {
    newPostExists () {
      return this.newPostText.trim().length > 0
    },
    displayedPosts () {
      return (this.showOnlyExcerpt && !this.isExcerptListExpanded) ? (this.posts || []).slice(0, this.numberOfVisiblePostsPerExcerptIteration) : this.posts
    },
    hasMorePosts () {
      return (this.showOnlyExcerpt && !this.isExcerptListExpanded) ? (this.posts && this.posts.length > this.numberOfVisiblePostsPerExcerptIteration) : false
    },
  },
  async created () {
    await this.loadPosts()
  },
  methods: {
    async loadPosts () {
      if (this.posts && this.posts.length) return
      this.posts = (await getStoreWall(this.storeId))
    },
    async writePost () {
      const text = this.newPostText.trim()
      if (!text) return
      try {
        showLoader()
        this.newPostText = ''
        const newPost = (await writeStorePost(this.storeId, text))
        this.posts.unshift(newPost)
      } catch (e) {
        console.error(e)
        pulseError(this.$i18n('wall.error-create'))
        this.newPostText = text
      } finally {
        hideLoader()
      }
    },
    async deletePost (postId) {
      try {
        showLoader()
        await deleteStorePost(this.storeId, postId)
        const index = this.posts.findIndex(post => post.id === postId)
        if (index >= 0) {
          this.posts.splice(index, 1)
        }
      } catch (e) {
        if (e.code === 403) {
          pulseError(this.$i18n('wall.error-delete'))
        } else {
          pulseError(this.$i18n('error_unexpected'))
          console.error(e.code)
        }
      } finally {
        hideLoader()
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.newpost textarea#newpost {
  overflow-y: auto !important;
}

ul.posts {
  margin: 0;

  &.has-more-posts {
    -webkit-mask-image: linear-gradient(to bottom, black 65%, var(--fs-color-transparent) 100%);
    mask-image: linear-gradient(to bottom, black 65%, var(--fs-color-transparent) 100%);
  }
}
</style>
