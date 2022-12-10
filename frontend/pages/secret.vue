<template>
  <h1>SECRET</h1>
</template>

<script setup>

definePageMeta({
  middleware: ["auth", "secret"]
})

</script>
