<template>
  <b-nav-item-dropdown
    id="dropdown-admin"
    v-b-tooltip="$i18n('menu.entry.administration')"
    right
    no-caret
  >
    <template v-slot:button-content>
      <i class="fas fa-cog" />
      <span class="d-md-none">
        {{ $i18n('menu.entry.administration') }}
      </span>
    </template>
    <b-dropdown-item
      v-for="item in items"
      :key="item.url"
      :href="item.url"
    >
      <i :class="item.icon" /> {{ item.label }}
    </b-dropdown-item>
  </b-nav-item-dropdown>
</template>
<script>
export default {
  props: {
    may: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    items () {
      // TODO: replace hard coded links with $url()
      const items = []
      if (this.may.administrateBlog) {
        items.push({
          url: this.$url('blogEdit'),
          icon: 'far fa-newspaper',
          label: this.$i18n('menu.blog')
        })
      }
      if (this.may.editQuiz) {
        items.push({
          url: this.$url('quizEdit'),
          icon: 'fas fa-question-circle',
          label: this.$i18n('menu.quiz')
        })
      }
      if (this.may.handleReports) {
        items.push({
          url: this.$url('reports'),
          icon: 'fas fa-exclamation',
          label: this.$i18n('menu.reports')
        })
      }
      if (this.may.administrateRegions) {
        items.push({
          url: this.$url('region'),
          icon: 'fas fa-map',
          label: this.$i18n('menu.manage_regions')
        })
      }
      if (this.may.administrateNewsletterEmail) {
        items.push({
          url: this.$url('email'),
          icon: 'fas fa-envelope',
          label: this.$i18n('menu.email')
        })
      }
      if (this.may.manageMailboxes) {
        items.push({
          url: this.$url('mailboxManage'),
          icon: 'far fa-envelope',
          label: this.$i18n('menu.manage_mailboxes')
        })
      }
      if (this.may.editFAQ) {
        items.push({
          url: this.$url('faqEdit'),
          icon: 'fas fa-question',
          label: this.$i18n('menu.faq')
        })
      }
      if (this.may.editContent) {
        items.push({
          url: this.$url('contentEdit'),
          icon: 'fas fa-file-alt',
          label: this.$i18n('menu.content')
        })
      }

      return items.sort((a, b) => a.label.localeCompare(b.label))
    }
  }
}
</script>

<style>

</style>
