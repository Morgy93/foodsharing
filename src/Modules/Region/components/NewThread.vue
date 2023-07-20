<!-- eslint-disable vue/max-attributes-per-line -->
<!-- eslint-disable vue/singleline-html-element-content-newline -->
<template>
  <div :class="{disabledLoading: isLoading}">
    <div>
      <div class="card-header text-white bg-primary">
        {{ $i18n('forum.new_thread') }}
      </div>
    </div>

    <div class="card-header text-black bg-white">
      <label class="font-weight-bold">{{ $i18n('forum.thread.title') }}*</label>
      <b-form-input id="forum-create-thread-form-title" v-model="title" />
      <div
        class="mt-3"
        v-html="$i18n('forum.markdown_description')"
      />
      <label class="font-weight-bold mt-3">{{ $i18n('forum.post.body') }}*</label>
      <b-form-textarea
        id="forum-create-thread-form-body"
        v-model="body"
        rows="6"
      />
      <label class="mt-3">{{ $i18n('forum.inform_per_email') }}*</label>
      <div class="row">
        <div class="col">
          <input id="send_mail_button" v-model="sendMail" class="mr-2" type="checkbox">
          {{ sendMailLabelText }}
        </div>
        <div class="col-auto">
          <button
            class="btn btn-primary btn-sm"
            @click="createNewThread"
          >
            {{ $i18n('button.create') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { createThread } from '@/api/forum'
import { pulseError } from '@/script'
import i18n from '@/helper/i18n'

export default {
  props: {
    groupId: { type: Number, required: true },
    subforumId: { type: Number, required: true },
  },
  data () {
    return {
      sendMail: false,
      body: null,
      title: null,
      isLoading: false,
    }
  },
  computed: {
    sendMailLabelText () {
      const deliveryMail = this.$i18n('forum.thread.delivery_mail.title')
      const activate = this.$i18n('forum.thread.delivery_mail.activate')
      const disable = this.$i18n('forum.thread.delivery_mail.disable')
      return this.sendMail ? deliveryMail + ' ' + disable : deliveryMail + ' ' + activate
    },
  },
  methods: {
    async createNewThread () {
      this.isLoading = true
      try {
        await createThread(this.groupId, this.subforumId, this.title, this.body, this.sendMail)
        // redirect to forum overview
        window.location = this.$url('forum', this.groupId, this.subforumId)
      } catch (err) {
        this.isLoading = false
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>

<style scoped>

</style>
