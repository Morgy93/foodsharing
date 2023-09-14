<template>
  <div class="wall">
    <div
      v-if="!readOnly"
      class="input-area"
    >
      <!-- TODO: unfinished -->
      <b-textarea
        placeholder="Nachricht schreiben..."
      />
      <b-button-group vertical>
        <b-button
          variant="primary"
          block
        >
          <i class="fas fa-image" />
        </b-button>
        <b-button
          variant="primary"
          block
        >
          <i class="fas fa-paper-plane" />
        </b-button>
      </b-button-group>
    </div>
    <div class="wall-posts">
      <table class="pintable">
        <tbody>
          <tr
            v-for="post in posts"
            :key="post.id"
            class="wall-post"
          >
            <td class="avatar">
              <a :href="$url('profile', post.author.id)">
                <Avatar
                  :url="post.author.avatar"
                  :size="50"
                />
              </a>
            </td>
            <td class="content">
              <div>
                <span class="meta-data">
                  <a :href="$url('profile', post.author.id)">{{ post.author.name }}</a>
                  ・
                  <small v-b-tooltip.hover.ds500="$dateFormatter.base(post.createdAt)">{{ $dateFormatter.relativeTime(post.createdAt) }}</small>
                  <i
                    class="fas fa-trash delete-post"
                  />
                  <!-- TODO: only show delete button when applicable -->
                  <!-- TODO: add delete button functionality -->
                </span>
                <span class="msg">
                  {{ post.body }}
                </span>
                <br>
                <div
                  v-if="post.pictures"
                  class="post-pictures"
                >
                  <b-img
                    v-for="(picture, i) in post.pictures"
                    :key="i"
                    :src="`/${picture.medium}`"
                    rounded
                  />
                </div>
                <!-- TODO: Add images -->
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import i18n from '@/helper/i18n'
import { getWallPosts, addPost, deletePost } from '@/api/wall'
import Avatar from '@/components/Avatar.vue'

export default {
  components: { Avatar },
  props: {
    target: {
      type: String,
      required: true,
    },
    targetId: {
      type: Number,
      required: true,
    },
    readOnly: {
      type: Boolean,
      default: false,
    },
  },
  data () {
    return {
      posts: [],
      text: '',
      isSending: false,
      error: false,
      mayDelete: false,
      mayPost: false,
    }
  },
  async created () {
    const data = await getWallPosts(this.target, this.targetId)
    this.posts = data.results
    this.mayDelete = data.mayDelete
    this.mayPost = data.mayPost
  },
  methods: {
    async addPost () {
      try {
        this.isSending = true
        const result = await addPost(this.target, this.targetId, this.text)
        this.posts.unshift(result.post)
      } catch (err) {
        this.error = true
      } finally {
        this.isSending = false
      }
    },
    async deletePost (post) {
      try {
        this.isSending = true
        await deletePost(this.target, this.targetId, post.id)
      } finally {
        this.isSending = false
        const id = this.posts.indexOf(post)
        if (id !== -1) {
          this.posts.splice(id, 1)
        }
      }
    },
    i18n (key) {
      return i18n(key)
    },
  },

}
</script>

<style lang="scss" scoped>
.wall{
  border: 1px solid var(--fs-border-default);
  border-radius: 6px;
}

.input-area {
  display:flex;
  textarea {
    min-height: 75px;
    border-radius: var(--border-radius) 0 0 0;
    border-width: 0 0 1px 0;
  }
  .btn-group-vertical .btn {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
  }
}

.wall-post{
  &:not(:last-child) {
    border-bottom: 1px solid var(--fs-border-default);
  }
  .avatar {
    margin: .25em
  }
  .content {
    vertical-align: top;
    width: 100%;
    padding-right: 1em;
    .meta-data{
      display: block;
      margin: .25em 0 .5em 0;
    }
    .delete-post {
      float: right;
      padding: .5em;
      cursor: pointer;
      position: relative;
      left: 1em;
    }
    .post-pictures {
      display: inline-block;
      border: 1px solid var(--fs-border-default);
      border-radius: var(--border-radius);
      background-color: var(--fs-color-light);
      padding: 3px;
      margin: 1em 0;
      img {
        max-height: 150px;
        margin: 3px;
      }
    }
  }
}
</style>
