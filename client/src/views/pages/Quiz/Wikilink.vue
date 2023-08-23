<template>
  <a :href="externalLink">
    {{ $i18n('quiz.wikilink') }}
  </a>
</template>

<script>
export default {
  props: {
    link: {
      type: String,
      required: true,
    },
  },
  computed: {
    externalLink () {
      let url = this.link
      if (!url.startsWith('http')) {
        url = 'https://' + url
      }
      return url
    },
  },
}
</script>
