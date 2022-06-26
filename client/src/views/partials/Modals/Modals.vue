<template>
  <div class="modals">
    <LanguageChooser />
    <SearchBarModal />
    <CICDModal />
  </div>
</template>

<script>
// Hidden Elements
import LanguageChooser from '@/components/Navigation/LanguageChooser'
import SearchBarModal from '@/components/SearchBar/SearchBarModal'
import CICDModal from '@/views/partials/CI-CD/CICDModal.vue'

export default {
  name: 'ModalPlaceholder',
  components: {
    LanguageChooser,
    SearchBarModal,
    CICDModal,
  },
}
</script>
