<template>
  <nav>
    <header>
      <NuxtLink to="/">Foodsharing Frontend Test</NuxtLink>
    </header>

    <NuxtLink
      v-for="locale in availableLocales"
      :key="locale"
      :to="switchLocalePath(locale.code)"
    >
        {{ locale.code }}
    </NuxtLink>

    <ul v-if="user">
      <li><p>{{ $t('hello', {name: user.firstname}) }}</p></li>
      <li><NuxtLink :to="localePath('/secret')">secret page (Not allowed)</NuxtLink></li>
      <li><NuxtLink :to="localePath(`/profile/${user.id}`)">Profile</NuxtLink></li>
      <li><NuxtLink :to="localePath('/store/overview')">Store overview</NuxtLink></li>
      <li><button @click="logout">{{ $t('account.logout') }}</button></li>
    </ul>
    <p v-else>
      <NuxtLink :to="localePath('/login')">{{ $t('account.login') }}</NuxtLink>
    </p>
  </nav>
</template>

<script setup>
const user = computed(() => useUser.get())

async function logout () {
  await useAuth.logout();
  navigateTo('/')
}

const availableLocales = useI18n().locales
</script>

<style>
nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

ul {
  align-items: center;
  display: flex;
  gap: 1rem;
  padding: 0;
  margin: 0;
}

li {
  list-style: none;
}
</style>
