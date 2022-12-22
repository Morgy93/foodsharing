<template>
  <div>
    <span><b><a :href="$url('profile', authorId)">{{ authorName }}:</a></b></span>
    <div>{{ message }}</div>
    <div>
      <small class="text-muted">{{ $dateFormatter.dateTime(sentAt) }}</small>
    </div>
    <hr>
  </div>
</template>

<script>
export default {
  name: 'Message',
  props: {
    authorName: { type: String, required: true },
    authorId: { type: Number, required: true },
    message: { type: String, required: true },
    sentAt: { type: String, required: true },
  },
}
</script>

<style scoped>

</style>
