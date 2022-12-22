<template>
  <div>
    <span><b>{{ authorName }}:</b></span>
    <div>{{ message }}</div>
    <div>
      <small class="text-muted">{{ sentAt.toLocaleString() }}</small>
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
    sentAt: { type: Date, required: true },
  },
}
</script>

<style scoped>

</style>
