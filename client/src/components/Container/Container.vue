<template>
  <div class="list-group">
    <div
      class="list-group-item list-group-header"
      @click="toggleExpanded"
    >
      <h5
        :class="{ 'expanded': isExpanded }"
        v-html="title"
      />
      <i
        :alt="isExpanded ? $i18n('dashboard.showmore') : $i18n('dashboard.showless')"
        class="fas fa-angle-down"
        :class="{ 'fa-rotate-180': isExpanded }"
      />
    </div>
    <slot v-if="isExpanded" />
    <button
      v-if="isExpanded && isToggleVisible && !isToggled"
      class="list-group-item list-group-item-secondary small font-weight-bold list-group-item-action text-center"
      @click="showFullList"
      v-html="$i18n('dashboard.showmore')"
    />
    <button
      v-else-if="isExpanded && isToggled"
      class="list-group-item small list-group-item-action font-weight-bold text-center"
      @click="reduceList"
      v-html="$i18n('dashboard.showless')"
    />
  </div>
</template>

<script>
export default {
  name: 'ToggleContainer',
  props: {
    tag: { type: String, default: 'tag' },
    title: { type: String, default: 'title' },
    toggleVisiblity: { type: Boolean, default: false },
  },
  data () {
    return {
      isToggled: false,
      isExpanded: true,
    }
  },
  computed: {
    isToggleVisible () {
      return this.toggleVisiblity
    },
  },
  created () {
    const state = this.getExpanded()
    if (state !== null) {
      this.setExpanded(state)
    }
  },
  methods: {
    toggleExpanded () {
      this.setExpanded(!this.isExpanded)
    },
    getExpanded () {
      return JSON.parse(localStorage.getItem(`expanded_${this.tag}`))
    },
    setExpanded (state) {
      this.isExpanded = state
      localStorage.setItem(`expanded_${this.tag}`, JSON.stringify(state))
    },
    showFullList () {
      this.isToggled = true
      this.$emit('show-full-list')
    },
    reduceList () {
      this.isToggled = false
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

  h5 {
    font-size: 0.8rem;
    font-weight: bold;
  }
}

.list-group-item:not(:last-child):not(.list-group-header):not(.list-row-item) {
  border-bottom: 0;
}

::v-deep .field {
  display: flex;
  min-height: 70px;

  &--stack {
    flex-direction: column;
    justify-content: space-between;
  }
}

::v-deep .field-container {
  display: flex;
  justify-content: space-between;
  width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  align-items: center;

  &--stack {
    flex-direction: column;
    align-items: start;
  }
}

::v-deep .field-headline {
  // flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: bold;
  font-size: 0.9rem;
  // white-space: nowrap;
  // display: flex;
  // align-items: center;

  &--big {
    font-size: 1rem;
  }
}

::v-deep .field-subline {
  display: inline-block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-right: 0.5rem;

  &--muted {
    color: var(--gray);
  }
}
</style>
