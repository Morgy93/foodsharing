<template>
  <div
    v-if="!isSet || isNew"
    class="information-field alert alert-danger d-flex justify-content-between"
  >
    <i
      v-if="icon"
      class="fas info-icon align-self-center mr-3 d-none d-md-block "
      :class="icon"
    />
    <div class="flex-grow-1 d-flex flex-column justify-content-between">
      <div class="alignt-self-start">
        <h4
          v-if="title"
          v-html="title"
        />
        <p
          v-if="description"
          class="description mb-1 w-md-50"
          v-html="description"
        />
      </div>
      <div
        v-if="links.length > 0"
        class="d-flex align-items-center my-2"
      >
        <a
          v-for="(link, key) in links"
          :key="key"
          class="btn btn-sm btn-danger font-weight-bold align-self-start mr-2"
          :href="link.urlShortHand ? $url(link.urlShortHand) : link.href"
          v-html="$i18n(link.text)"
        />
      </div>
    </div>
    <i
      v-if="isCloseable"
      class="close-interaction fas fa-times"
      @click="close"
    />
  </div>
</template>

<script>
export default {
  props: {
    type: { type: String, default: 'error' },
    tag: { type: String, default: '' },
    icon: { type: String, default: '' },
    title: { type: String, default: '' },
    description: { type: String, default: '' },
    isTimeBased: { type: Boolean, default: false },
    time: { type: String, default: '' },
    isCloseable: { type: Boolean, default: true },
    links: { type: Array, default: () => [] },
  },
  data () {
    return {}
  },
  computed: {
    isSet () {
      return JSON.parse(localStorage.getItem(this.tag))
    },

    isNew () {
      const storage = JSON.parse(localStorage.getItem(`${this.tag}_time`))
      return new Date(this.time) > new Date(storage)
    },
  },
  created () {
    if (this.isNew) {
      localStorage.removeItem(this.tag)
      localStorage.removeItem(`${this.tag}_time`)
    }
  },
  mounted () {
    if (this.isSet && !this.isNew) {
      this.remove()
    }
  },
  methods: {
    setSeen () {
      if (this.isCloseable) {
        localStorage.setItem(this.tag, JSON.stringify(true))

        if (this.isTimeBased) {
          localStorage.setItem(`${this.tag}_time`, JSON.stringify(new Date()))
        }
      }
    },
    close () {
      this.setSeen()
      this.remove()
    },
    remove () {
      this.$emit('close')
      this.$destroy()
      this.$el.parentNode.removeChild(this.$el)
    },
  },
}
</script>

<style lang="scss" scoped>
.information-field {
  min-height: 100px;
}

.alert-broadcast {
  min-height: 0;
  align-content: center;
}

.info-icon {
  font-size: 3rem;
}

.close-interaction {
  cursor: pointer;

  &:hover {
    color: var(--primary);
  }
}

.list-group-item-action {
  cursor: pointer;
}

::v-deep.description {
  a {
    text-decoration: underline;
  }
  & * {
      margin-bottom: 0 !important;
  }
}
</style>
