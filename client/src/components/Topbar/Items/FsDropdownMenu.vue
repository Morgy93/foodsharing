<template>
  <b-nav-item-dropdown
    ref="dropdown-menu"
    v-b-tooltip="!showTitle ? $i18n(title) : ''"
    class="item"
    :right="right"
    :lazy="lazy"
  >
    <template #button-content>
      <slot name="heading-icon">
        <i
          v-if="icon"
          class="icon"
          :class="`fas ${icon}`"
        />
      </slot>
      <slot name="badge">
        <span
          v-if="badge"
          class="badge badge-danger"
        >
          {{ badge }}
        </span>
      </slot>
      <slot name="heading-text">
        <span
          v-if="title"
          class="headline"
          :class="{'show': showTitle}"
          v-html="$i18n(title)"
        />
        <span
          v-if="title"
          class="hide-for-users"
          v-html="$i18n(title)"
        />
      </slot>
    </template>
    <li
      :class="{'scroll-container': scrollbar}"
    >
      <slot name="content">
        <template v-for="heading in items">
          <h6
            :key="heading.heading"
            class="dropdown-header"
          >
            {{ $i18n(heading.heading) }}
          </h6>
          <a
            v-for="item in heading.menuItems"
            :key="item.url"
            :href="$url(item.url)"
            class="dropdown-item sub"
            role="menuitem"
            :target="item.target ? item.target : ''"
            :rel="item.target === '_blank' ? 'noopener noreferrer nofollow' : '' "
          >
            {{ $i18n(item.menuTitle) }}
          </a>
        </template>
      </slot>
    </li>
    <li class="actions">
      <!-- eslint-disable-next-line vue/max-attributes-per-line -->
      <slot name="actions" :hide="closeDropdownMenu" />
    </li>
  </b-nav-item-dropdown>
</template>
<script>

export default {
  props: {
    title: {
      type: String,
      default: undefined,
    },
    badge: {
      type: [String, Number],
      default: undefined,
    },
    items: {
      type: Array,
      default: undefined,
    },
    icon: {
      type: String,
      default: undefined,
    },
    lazy: {
      type: Boolean,
      default: true,
    },
    right: {
      type: Boolean,
      default: false,
    },
    scrollbar: {
      type: Boolean,
      default: false,
    },
    showTitle: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    closeDropdownMenu () {
      this.$refs['dropdown-menu'].hide()
    },
  },
}
</script>

<style lang="scss" scoped>
.item {
  text-align: center;
}

::v-deep .nav-link{
  position: relative;
}

.headline {
  display: none;

  .collapse.show &,
  &.show {
    display: inline-block;
  }
}

.scroll-container {
  overflow-y: auto;
  overflow-x: hidden;
  max-height: 64vh;
}

::v-deep.item a::after {
  opacity: .25;
  @media (max-width: 576px) {
     display: none;
  }
}

::v-deep .dropdown-item {
  font-size: 0.8rem;
}

.actions {
  padding-top: 0.5rem;
  margin-top: 0.5rem;
  border-top: 1px solid var(--border);

  &:empty {
    display: none;
  }
}

::v-deep .text-truncate {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
