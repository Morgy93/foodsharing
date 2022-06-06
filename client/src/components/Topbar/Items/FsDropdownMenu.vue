<template>
  <b-nav-item-dropdown
    ref="dropdown-menu"
    v-b-tooltip="$i18n(menuTitle)"
    :right="right"
    :lazy="lazy"
  >
    <template #button-content>
      <slot name="heading-icon">
        <i
          v-if="icon"
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
          v-if="menuTitle"
          class="headline"
          :class="{'d-sm-inline-block': showTitle}"
          v-html="$i18n(menuTitle)"
        />
      </slot>
    </template>
    <div class="scroll-container">
      <slot name="content">
        <template v-for="heading in items">
          <h3
            :key="heading.heading"
            class="dropdown-header"
          >
            {{ $i18n(heading.heading) }}
          </h3>
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
    </div>
    <div class="d-flex flex-column actions">
      <!-- eslint-disable-next-line vue/max-attributes-per-line -->
      <slot name="actions" :hide="closeDropdownMenu" />
    </div>
  </b-nav-item-dropdown>
</template>
<script>

export default {
  props: {
    menuTitle: {
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
      default: false,
    },
    right: {
      type: Boolean,
      default: false,
    },
    showTitle: { type: Boolean, default: false },
  },
  methods: {
    closeDropdownMenu () {
      this.$refs['dropdown-menu'].hide()
    },
  },
}
</script>

<style lang="scss" scoped>
.headline {
  display: none;

  .collapse.show & {
    display: inline-block;
  }
}

.badge {
  position: absolute;
  top: 0;
  left: 20px;

  .collapse.show & {
    left: 10px;
  }
}

::v-deep .dropdown-toggle::after {
  content: unset;
}

@media(max-width: 767px) {
  .collapse {
    .dropdown ::v-deep .dropdown-menu {
      // Margin to have an indent in the burger menu.
      margin-left: 30px;
    }
  }
}
.dropdown {
  &.list-with-actions ::v-deep .dropdown-menu {
    padding: 0;
  }
  ::v-deep .dropdown-menu {
    overflow: hidden;
    // Bug of chrome: https://bugs.chromium.org/p/chromium/issues/detail?id=957946
    background-clip: unset;

    min-width: 300px;
    max-width: 300px;
    box-shadow: 0 0 7px rgba(0, 0, 0, 0.3);

    .scroll-container {
      // LibSass is deprecated: https://github.com/sass/libsass/issues/2701
      max-height: unquote("min(340px, 70vh)");
      overflow: auto;
    }
    .dropdown-item {
        i {
        display: inline-block;
        width: 1.7em;
        text-align: center;
        margin-left: -0.4em;
      }
    }

    .actions:not(:empty){
      padding-top: 0.5rem;
      border-top: 1px solid #e9ecef;
    }

    .actions .btn{
      flex: 1 1 auto;
      margin: 0 1px;
    }
    .sub {
      padding-left: 2.2rem;
      font-size: 0.9rem;
    }
    .dropdown-header {
     font-weight: bold;
    }
    .group .dropdown-header {
      color: black;
    }
  }
  @media (max-width: 575px) {
    position: initial;
    ::v-deep .dropdown-menu {
      width: 100%;
      max-width: initial;
      top: 45px;

      .scroll-container {
        width: 100%;
      }
    }
  }

  ::v-deep .dropdown-toggle {
    position: relative;
  }
}
</style>
