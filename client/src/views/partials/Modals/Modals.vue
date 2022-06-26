<template>
  <div class="modals">
    <LanguageChooser />
    <SearchBarModal />
  </div>
</template>

<script>
// Hidden Elements
import LanguageChooser from '@/components/Navigation/LanguageChooser'
import SearchBarModal from '@/components/SearchBar/SearchBarModal'

export default {
  name: 'ModalPlaceholder',
  components: {
    LanguageChooser,
    SearchBarModal,
  },
}
</script>
