<template>
  <div class="container">
    <h1>
      {{ $t('account.login_form.headline') }}
    </h1>
    <div>
      <button @click="login">
        {{ $t('account.login') }}
      </button>
    </div>
  </div>
</template>

<script setup>

definePageMeta({
  middleware: ["no-auth"]
})

async function login() {

  // const test = await $fetch('/api/stores/meta-data');

  // console.log(test);

  const data = await $fetch(`https://dummyjson.com/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({

        username: 'kminchelle',
        password: '0lelplR',
      })
    })


  useAuth.login(data)
  navigateTo('dashboard')
}
</script>
