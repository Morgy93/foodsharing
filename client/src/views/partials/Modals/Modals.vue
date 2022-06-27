<template>
  <div class="bootstrap modals">
    <LanguageChooser />
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
import LanguageChooser from '@/components/Navigation/LanguageChooser.vue'
import SearchBarModal from '@/components/SearchBar/SearchBarModal.vue'
import StyleguideModal from '@/views/partials/Styleguide/StyleguideModal.vue'
import JoinRegionModal from '@/components/JoinRegion/JoinRegionModal.vue'

export default {
  name: 'ModalPlaceholder',
  components: {
    LanguageChooser,
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
