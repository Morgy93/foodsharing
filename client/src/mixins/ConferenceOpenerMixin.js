export default {
  data: function () {
    return {
      conferenceId: null,
    }
  },
  methods: {
    async showConferencePopup (id) {
      const modal = await this.$bvModal.msgBoxConfirm(this.$i18n('conference.description_text') + '\n' + this.$i18n('conference.privacy_notice'), {
        modalClass: 'bootstrap',
        title: this.$i18n('conference.join_title'),
        cancelTitle: this.$i18n('button.cancel'),
        okTitle: this.$i18n('conference.join'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (modal) {
        this.conferenceId = id
        this.join()
      }
    },
    join () {
      window.open(`/api/groups/${this.conferenceId}/conference?redirect=true`)
    },
  },
}
