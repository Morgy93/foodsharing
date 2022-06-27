<template>
  <div class="modals">
    <LanguageChooser />
    <SearchBarModal />
    <CICDModal />
    <JoinRegionModal /> <!--INCOMPLETE-->
  </div>
</template>

<script>
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
}
</script>
