<template>
  <div>
    <Container :title="title">
      <div
        v-for="responsibleUser in responsibleUsers"
        :key="responsibleUser.id"
      >
        <Avatar :url="responsibleUser.imageUrl" />
        {{ responsibleUser.user.name }}
      </div>
    </container>
  </div>
</template>

<script>
import Container from '@/components/Container/Container.vue'
import Avatar from '@/components/Avatar.vue'

export default {
  components: { Avatar, Container },
  props: {
    title: { type: String, required: true },
    responsibleUsers: { type: Array, required: true },
  },
}
</script>
