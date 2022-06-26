<template>
  <li class="nav-item dropdown">
    <button
      :id="title"
      class="nav-link dropdown-toggle"
      role="button"
      data-toggle="dropdown"
      aria-expanded="false"
    >
      <slot name="icon">
        <i
          v-if="icon"
          class="icon-nav fas"
          :class="icon"
        />
      </slot>
      <slot name="badge">
        <span
          v-if="badge"
          class="badge badge-danger"
          :class="{
            'onlyNine': String(badge).length === 1,
            'overNinetyNine': String(badge).length > 2,
          }"
          v-html="badge"
        />
      </slot>
      <slot name="text">
        <span
          class="nav-text"
          v-html="title"
        />
      </slot>
    </button>
    <div
      class="dropdown-menu"
      :class="{
        'dropdown-menu-right': direction === 'right'
      }"
      :aria-labelledby="title"
    >
      <li
        class="content"
        :class="{
          'dropdown-menu-scrollable': scrollable
        }"
      >
        <slot name="content">
          <a
            class="dropdown-item"
            href="#"
          >
            Action
          </a>
          <a
            class="dropdown-item"
            href="#"
          >
            Another action
          </a>
          <div class="dropdown-divider" />
          <a
            class="dropdown-item"
            href="#"
          >
            Something else here
          </a>
        </slot>
      </li>
      <li class="actions">
        <slot
          name="actions"
          :hidden="false"
        >
          <a
            class="dropdown-item dropdown-action"
            href="#"
          >
            Action
          </a>
          <a
            class="dropdown-item dropdown-action"
            href="#"
          >
            Another action
          </a>
          <div class="dropdown-divider" />
          <a
            class="dropdown-item dropdown-action"
            href="#"
          >
            Something else here
          </a>
        </slot>
      </li>
    </div>
  </li>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: 'Dropdown',
    },
    icon: {
      type: String,
      default: undefined,
    },
    direction: {
      type: String,
      default: 'left',
    },
    badge: {
      type: [String, Number],
      default: 0,
    },
    scrollable: {
      type: Boolean,
      default: false,
    },
  },
}
</script>

<style lang="scss" scoped>
@import "./NavItems.scss";

.actions {
  margin-top: .5rem;
  padding-top: .5rem;
  border-top: 1px solid var(--fs-color-primary-100);
}

.dropdown {

  @media (max-width: 768px) {
    position: unset;
  }

  .dropdown-submenu {
    width: 100%;
  }

  .dropdown-menu {
    min-width: 420px;
    max-width: 420px;

    @media (max-width: 768px) {
      width: 100%;
      min-width: unset;
      max-width: unset;
    }
  }

  .dropdown-toggle::after {
    @media (max-width: 768px) {
      display: none;
    }
  }
}

.dropdown-action {
  font-size: 0.8rem;
  display: flex;
  align-items: center;

  padding-top: 0.35rem;
  padding-bottom: 0.35rem;
}

.dropdown-menu-scrollable {
  max-height: 65vh;
  overflow-y: auto;
}
</style>
