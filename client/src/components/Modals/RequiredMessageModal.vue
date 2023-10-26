<template>
  <b-modal
    :id="id"
    :title="$i18n('required_messages.sure_title')"
    :cancel-title="$i18n('button.cancel')"
    :ok-title="$i18n('button.yes_i_am_sure')"
    centered
    @ok="resolveCallback?.(optionalMessage)"
    @cancel="rejectCallback?.()"
  >
    <p>
      <span v-html="$i18n(`required_messages.${messageKey}.really`, params)" />
      {{ $i18n(`required_messages.message_info`, params) }}
    </p>
    <blockquote>
      <div>{{ $i18n('salutation.3') }} {{ params.name }},</div>
      <div>{{ $i18n(`required_messages.${messageKey}.main`, params) }}</div>
      <br>
      <b-form-textarea
        v-model="optionalMessage"
        :placeholder="$i18n('required_messages.placeholder')"
        max-rows="4"
        maxlength="3000"
      />
      <br>
      <div>{{ $i18n(`required_messages.${messageKey}.footer`) }}</div>
    </blockquote>
  </b-modal>
</template>

<script>
export default {
  props: {
    messageKey: { type: String, required: true },
    initialParams: { type: Object, default: () => ({}) },
    identifier: { type: String, default: '' },
  },
  data () {
    return {
      params: Object.assign({ name: '' }, this.initialParams),
      optionalMessage: '',
      resolveCallback: null,
      rejectCallback: null,
    }
  },
  computed: {
    id () {
      return `requiredMessageModal-${this.messageKey}-${this.identifier}`
    },
  },
  methods: {
    show (params = {}) {
      this.params = Object.assign(this.params, params)
      this.$bvModal.show(this.id)
    },
    getConfirmationPromise () {
      this.rejectCallback?.()
      return new Promise((resolve, reject) => {
        this.resolveCallback = resolve
        this.rejectCallback = reject
      })
    },
  },
}
</script>

<style>

</style>
