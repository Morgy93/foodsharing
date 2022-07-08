<template>
  <div>
    <JoinRegionModal />
    <LanguageChooserModal />
    <SearchBarModal />
    <StyleGuideModal />
  </div>
</template>

<script>
// Modals
import JoinRegionModal from './JoinRegionModal.vue'
import LanguageChooserModal from './LanguageChooserModal.vue'
import SearchBarModal from './SearchBarModal.vue'
import StyleGuideModal from './StyleGuideModal.vue'

export default {
  name: 'ModalLoader',
  components: {
    JoinRegionModal,
    LanguageChooserModal,
    SearchBarModal,
    StyleGuideModal,
  },
}
</script>
