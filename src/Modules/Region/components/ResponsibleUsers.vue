<template>
  <div>
    <Container
      :title="title"
      class="bg-white"
      :toggle-visiblity="showToggleVisiblity"
    >
      <div class="row">
        <div
          v-for="responsibleUser in responsibleUsers"
          :key="responsibleUser.id"
          class="col-md-4 mb-2 mt-2"
        >
          <div class="text-center">
            <Avatar :url="responsibleUser.imageUrl" />
            <p>{{ responsibleUser.user.name }}</p>
          </div>
        </div>
      </div>
    </Container>
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
  computed: {
    showToggleVisiblity () {
      return this.responsibleUsers.length >= 7
    },
  },
}
</script>
