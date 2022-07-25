<template>
  <div
    class="informations-wrapper position-relative"
    :class="{ 'multiply': count > 1, 'single': count <= 1 }"
  >
    <InformationField
      v-for="(prompt, key) in filteredList"
      :key="key"
      :type="prompt.type"
      :tag="prompt.tag"
      :icon="prompt.icon"
      :title="prompt.title"
      :description="prompt.description"
      :is-time-based="prompt.isTimeBased"
      :time="prompt.time"
      :is-closeable="prompt.isCloseable"
      :links="prompt.links"
    />
  </div>
</template>

<script>
// Stores
import { getters } from '@/stores/user'
// Components
import InformationField from './InformationField.vue'
// Mixin
import RouteAndDeviceCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'

export default {
  components: {
    InformationField,
  },
  mixins: [RouteAndDeviceCheckMixin],
  props: {
    allVisible: { type: Boolean, default: false },
    list: { type: Array, default: () => [] },
  },
  computed: {
    filteredList () {
      return this.list.filter(prompt =>
        (prompt.type === 'calendar' && getters.isFoodsaver() && !this.hasCalendarToken) ||
        (prompt.type === 'push' && !this.isSafari))
    },
    count () {
      return this.filteredList.length
    },
    hasCalendarToken: () => getters.hasCalendarToken(),
  },
}
</script>

<style lang="scss" scoped>
.informations-wrapper:empty {
  display: none;
}

.single * {
  margin-bottom: 1rem;
}

.multiply {
  margin-bottom: 2rem;
}

.multiply *:nth-child(n+2) {
  display: none !important;
}

.multiply::after {
  background-color: currentColor;
  border-radius: var(--border-radius);
  content: '';
  height: 100%;
  left: 0;
  opacity: 0.15;
  position: absolute;
  top: 0;
  transform: translateY(14px) scale(0.95);
  width: 100%;
  z-index: -1;
}
</style>
