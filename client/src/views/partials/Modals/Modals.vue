<template>
  <div class="modals">
    <LanguageChooser />
    <div v-if="isLoggedIn">
      <SearchBarModal />
      <CICDModal />
      <JoinRegionModal /> <!--INCOMPLETE-->
    </div>
  </div>
</template>

<script>
// Stores
import DataUser from '@/stores/user'
// Hidden Elements
import LanguageChooser from '@/components/Navigation/LanguageChooser'
import SearchBarModal from '@/components/SearchBar/SearchBarModal'
import CICDModal from '@/views/partials/CI-CD/CICDModal.vue'
import JoinRegionModal from '@/components/JoinRegion/JoinRegionModal'

export default {
  name: 'ModalPlaceholder',
  components: {
    LanguageChooser,
    SearchBarModal,
    CICDModal,
    JoinRegionModal,
  },
  computed: {
    isLoggedIn () {
      return DataUser.getters.isLoggedIn()
    },
  },
}
</script>
