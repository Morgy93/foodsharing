
<template>
  <div>
    <p v-if="form.error" mb-3 text-red-500>
      {{ form.error }}
    </p>
    <form @submit.prevent="onLoginClick">
      <input v-model="form.data.email" autocomplete="username" type="email" placeholder="Email" required>
      <input v-model="form.data.password" autocomplete="current-password" type="password" placeholder="Password" required>
      <button type="submit" :disabled="form.pending">
        Login
      </button>
    </form>
  </div>
</template>

<script setup>
  definePageMeta({
    middleware: ["redirect-to-dashboard-when-loggedin"]
  })
// const emit = defineEmits(['success'])
// const useAuth = useAuth();
const form = reactive({
  data: {
    email: 'userbot@example.com',
    password: 'user',
  },
  error: '',
  pending: false,
})

async function onLoginClick() {
  try {
    form.error = ''
    form.pending = true

    await useAuth.login(form.data)
    navigateTo('/')
  }
  catch (error) {
    console.error(error)
    if (error.data.message) {
      form.error = error.data.message
    }
  }
  finally {
    form.pending = false
  }
}
</script>
