<template>
  <div class="list-group">
    <div
      class="list-group-item list-group-header"
      @click="toggleExpanded"
    >
      <strong
        :class="{ 'expanded': expanded }"
        v-html="title"
      />
      <i
        class="fas fa-angle-down"
        :class="{ 'fa-rotate-180': expanded }"
      />
    </div>
    <slot v-if="expanded" />
    <button
      v-if="expanded && !hidden && !toggled"
      class="list-group-item list-group-item-action list-group-expand"
      @click="showFullList"
      v-html="$i18n('dashboard.showmore')"
    />
    <button
      v-else-if="expanded && toggled"
      class="list-group-item list-group-item-action list-group-expand"
      @click="reduceList"
      v-html="$i18n('dashboard.showless')"
    />
  </div>
</template>

<script>
export default {
  props: {
    tag: { type: String, default: 'tag' },
    title: { type: String, default: 'title' },
    hide: { type: Boolean, default: false },
  },
  data () {
    return {
      toggled: false,
      hidden: this.hide,
      expanded: true,
    }
  },
  created () {
    const state = this.getExpanded()
    if (state !== null) {
      this.setExpanded(state)
    }
  },
  methods: {
    toggleExpanded () {
      this.setExpanded(!this.expanded)
    },
    getExpanded () {
      return JSON.parse(localStorage.getItem(`expanded_${this.tag}`))
    },
    setExpanded (state) {
      this.expanded = state
      localStorage.setItem(`expanded_${this.tag}`, JSON.stringify(state))
    },
    showFullList () {
      this.toggled = true
      this.$emit('show-full-list')
    },
    reduceList () {
      this.toggled = false
      this.$emit('reduce-list')
    },
  },
}
</script>

<style lang="scss" scoped>
.list-group {
  min-width: 250px;
  margin-bottom: 1rem;

  &:last-child {
    margin-bottom: 0;
  }
}

.list-group-header,
.list-group-expand {
  cursor: pointer;
  padding: 0 1rem;
  display: flex;
  align-items: center;
  min-height: 40px;
}

.list-group-header {
  background-color: var(--primary);
  color: var(--white);
  justify-content: space-between;
}

.list-group-expand {
  font-size: 80%;
  color: var(--primary);
  justify-content: center;
}

.list-group-item:not(:last-child):not(.list-group-header) {
  border-bottom: 0;
}
</style>
