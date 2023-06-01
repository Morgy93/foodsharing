<script>
export default {
  props: {
  },
  data () {
    return {
      visible: false,
    }
  },
  methods: {
    toggle () {
      this.visible = !this.visible
    },
  },
}
</script>

<template>
  <div class="card rounded mb-2">
    <div
      class="card-header text-white bg-primary d-flex justify-content-between corner-all "
      @click.prevent="toggle"
    >
      <slot name="header" />

      <a
        class="px-1 text-light"
        href="#"
        @click.prevent.stop="toggle"
      >
        <i :class="['fas fa-fw', `fa-chevron-${visible ? 'down' : 'up'}`]" />
      </a>
    </div>

    <div
      v-show="visible"
      class="card-body bg-white p-2"
    >
      <slot />
    </div>
  </div>
</template>
