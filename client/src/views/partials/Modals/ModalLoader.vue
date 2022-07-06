<template>
  <div>
    <JoinRegionModal />
    <LanguageChooserModal />
    <SearchBarModal />
    <StyleGuideModal />
    <MarkdownPreviewModal />
  </div>
</template>

<script>
// Modals
import JoinRegionModal from './JoinRegionModal.vue'
import LanguageChooserModal from './LanguageChooserModal.vue'
import SearchBarModal from './SearchBarModal.vue'
import StyleGuideModal from './StyleGuideModal.vue'
import MarkdownPreviewModal from './MarkdownPreviewModal.vue'

export default {
  name: 'ModalLoader',
  components: {
    JoinRegionModal,
    LanguageChooserModal,
    SearchBarModal,
    StyleGuideModal,
    MarkdownPreviewModal,
  },
}
</script>
