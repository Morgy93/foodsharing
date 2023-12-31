<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <li :id="`thread-${thread.id}`" class="thread">
    <a class="ui-corner-all d-flex" :href="threadUrl">
      <span class="user-picture mr-2">
        <Avatar
          :url="thread.lastPost.author.avatar"
          :is-sleeping="thread.lastPost.author.sleepStatus"
          :size="50"
        />
      </span>
      <div class="text-truncate flex-grow-1 d-flex flex-wrap-reverse flex-sm-nowrap">
        <div
          class="text-truncate thread-main px-1 mr-1 align-self-center flex-grow-1 flex-shrink-1 flex-sm-noshrink"
          :class="{'font-weight-bold': thread.isSticky}"
        >
          <span class="thread-title d-inline" :class="titleClass">
            <i v-if="isClosed" class="fas fa-lock" :title="$i18n('forum.thread.closed')" />
            {{ thread.title }}
          </span>
        </div>
        <div
          v-b-tooltip="$dateFormatter.dateTime(lastPostDate)"
          class="last-post p-1 ui-corner-all align-self-center"
        >
          <span class="info d-inline d-sm-block">
            {{ $i18n('forum.from', { name: thread.lastPost.author.name || '' }) }}
          </span>
          <span class="time d-inline d-sm-block">
            {{ $dateFormatter.relativeTime(lastPostDate) }}
          </span>
        </div>
      </div>
    </a>
  </li>
</template>

<script>
import Avatar from '@/components/Avatar'
import ThreadStatus from './ThreadStatus'

export default {
  components: { Avatar },
  props: {
    thread: { type: Object, required: true },
  },
  computed: {
    threadUrl () {
      return this.$url('forum', this.thread.regionId, this.thread.regionSubId, this.thread.id)
    },
    lastPostDate () {
      return new Date(Date.parse(this.thread.lastPost.createdAt))
    },
    isClosed () {
      return this.thread.status === ThreadStatus.THREAD_CLOSED
    },
    titleClass () {
      return this.isClosed ? 'thread-title-closed' : ''
    },
  },
}
</script>

<style lang="scss" scoped>
.thread {
  // hardcoded image width as `min-width` to ensure flexbox honors it
  .user-picture {
    min-width: 64px;
    width: 64px;
    height: 50px;
    background-image: url('/img/forum_bubble.png');
  }

  .thread-title {
    font-size: 1.1em;
  }
  .thread-title-closed {
    color: var(--fs-color-gray-500);
  }

  .last-post {
    min-width: 150px;
    width: 150px;
    font-size: 0.9em;

    @media screen and (max-width: 575px) {
      width: 100%;
    }
  }

  // because this is inside `.linklist` we need to control hover behavior
  // by overriding text color and some background color / highlights.
  &:hover {
    .user-picture,
    .thread-title {
      color: var(--fs-color-light);
    }

    .user-picture .avatar ::v-deep img {
      box-shadow: 0 0 0 1px var(--fs-color-light);
    }

    @media screen and (min-width: 576px) {
      .last-post {
        &, .info, .time {
          color: var(--fs-color-primary-500);
          background-color: var(--fs-color-light);
        }
      }
    }
  }
}
</style>
