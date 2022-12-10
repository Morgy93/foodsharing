<template>
  <div class="container">
    <h1>{{ $t('index.headline') }}</h1>
    <p>{{ $t('index.subline') }}</p>
  </div>
</template>

<script setup>
  definePageMeta({
    middleware: ["redirect-to-dashboard-when-loggedin"]
  })
</script>
