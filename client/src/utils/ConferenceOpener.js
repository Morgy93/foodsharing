import { BModal } from 'bootstrap-vue'

import i18n from '@/i18n'

export default {
  components: { BModal },
  data: function () {
    return {
      conferenceId: null,
    }
  },
  methods: {
    async showConferencePopup (id) {
      const modal = await this.$bvModal.msgBoxConfirm(i18n('conference.description_text') + '\n' + i18n('conference.privacy_notice'), {
        modalClass: 'bootstrap',
        title: i18n('conference.join_title'),
        cancelTitle: i18n('button.cancel'),
        okTitle: i18n('conference.join'),
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
