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
          class="nav-icon fas"
          :class="icon"
        />
      </slot>
      <slot name="badge">
        <span
          v-if="badge"
          class="badge badge-danger"
          :class="{
            'onlyNine': badge.length === 1,
            'overNinetyNine': badge.length > 2,
          }"
          v-html="badge"
        />
      </slot>
      <span class="nav-text">{{ title }}</span>
    </button>
    <div
      class="dropdown-menu"
      :class="{ 'dropdown-menu-right': direction === 'right' } "
      :aria-labelledby="title"
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
      <li class="actions">
        <slot name="actions">
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
  & i {
    display: inline-flex;
    justify-content: center;
    align-content: center;
    width: 1rem;
    margin-right: 0.5rem;
  }
}
</style>
