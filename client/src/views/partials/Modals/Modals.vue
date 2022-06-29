<template>
  <div class="bootstrap modals">
    <LanguageChooserModal />
    <div v-if="isLoggedIn">
      <SearchBarModal />
      <StyleguideModal />
      <JoinRegionModal /> <!--INCOMPLETE-->
    </div>
  </div>
</template>

<script>
// Stores
import DataUser from '@/stores/user'
// Hidden Elements
import LanguageChooserModal from '@/components/LanguageChooser/LanguageChooserModal.vue'
import SearchBarModal from '@/components/SearchBar/SearchBarModal.vue'
import StyleguideModal from '@/views/partials/Styleguide/StyleguideModal.vue'
import JoinRegionModal from '@/components/JoinRegion/JoinRegionModal.vue'

export default {
  name: 'ModalPlaceholder',
  components: {
    LanguageChooserModal,
    SearchBarModal,
    StyleguideModal,
    JoinRegionModal,
  },
  computed: {
    isLoggedIn () {
      return DataUser.getters.isLoggedIn()
    },
  },
}
</script>
