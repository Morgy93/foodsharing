<template>
  <div class="bootstrap">
    <div class="page-container page-simple">
      <div class="row">
        <div class="col-4">
          <ConversationCard :class="['border']" />
        </div>

        <div class="col-8">
          <p>1234131</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ConversationCard from '@/components/Message/LiteChat/ConversationCard.vue'

export default {
  name: 'LiteChat',
  components: {
    ConversationCard,
  },
}
</script>

<style scoped>

</style>
