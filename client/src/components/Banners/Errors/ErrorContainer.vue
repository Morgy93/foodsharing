<template>
  <div class="error__container">
    <ErrorField
      v-for="(entry, key) in list"
      :key="key"
      :entry="entry"
    />
    <ModalLoader />
  </div>
</template>

<script>
// Stores
import DataUser from '@/stores/user'
// components
import ErrorField from './ErrorField.vue'
import ModalLoader from '@/views/partials/Modals/ModalLoader.vue'

export default {
  components: {
    ErrorField,
    ModalLoader, // is required because the website is not a single vue instance (each module has its own instance)  and otherwise the modals are only over datastore accessable and not by the global $bvmodal function
  },
  data () {
    return {
      list: [],
    }
  },
  async mounted () {
    await DataUser.mutations.fetchDetails()
    /**
     * Shows an error when the user has an invalid mobile phone number
     */
    const mobilePhoneNumber = DataUser.getters.getMobilePhoneNumber()
    if (mobilePhoneNumber && !this.$isValidPhoneNumber(mobilePhoneNumber)) {
      this.list.push({
        field: 'invalid_mobile_phonenumber',
        links: [{
          text: 'error.invalid_mobile_phonenumber.link',
          urlShortHand: 'settings',
        }],
      })
    }
    /**
     * Shows an error when the user has no avatar
     */
    if (DataUser.getters.getAvatar() === null) {
      this.list.push({
        field: 'missing_user_avatar',
        links: [{
          text: 'error.missing_user_avatar.link',
          urlShortHand: 'settings',
        }],
      })
    }
    /**
     * Shows an error when the user has an old avatar
     */
    if (DataUser.getters.getAvatar() && !DataUser.getters.getAvatar().startsWith('/api/uploads/')) {
      this.list.push({
        field: 'old_user_avatar',
        link: DataUser.getters.getAvatar(),
        links: [{
          text: 'error.old_user_avatar.link',
          urlShortHand: 'settings',
        }],
      })
    }
    /**
     * Shows an error when the user has no home region
     */
    if (DataUser.getters.isFoodsaver() && !DataUser.getters.hasHomeRegion()) {
      this.$bvModal.show('joinRegionModal')
      this.list.push({
        field: 'missing_home_region',
        links: [{
          text: 'error.missing_home_region.link',
          modal: 'joinRegionModal',
        }],
      })
    }
    /**
     * Shows an error when the user has no valid geolocation
     */
    if (DataUser.getters.isFoodsaver() && !DataUser.getters.hasLocations()) {
      this.list.push({
        field: 'missing_geolocation',
        links: [{
          text: 'error.missing_geolocation.link',
          urlShortHand: 'settings',
        }],
      })
    }
    /**
     * Shows an error when the user needs to activate his email
     */
    if (!DataUser.getters.hasActiveEmail()) {
      this.list.push({
        field: 'mail_activation',
        links: [{
          text: 'error.mail_activation.link_1',
          urlShortHand: 'resendActivationMail',
        },
        {
          text: 'error.mail_activation.link_2',
          urlShortHand: 'settings',
        }],
      })
    }
    /**
     * Shows an error when the user has a bouncing email
     */
    if (DataUser.getters.hasBouncingEmail()) {
      this.list.push({
        field: 'mail_bounce',
        links: [{
          text: 'error.mail_bounce.link_1',
          urlShortHand: 'settings',
        },
        {
          text: 'error.mail_bounce.link_2',
          urlShortHand: 'freshdesk_locked_email',
        }],
      })
    }
  },
}
</script>
