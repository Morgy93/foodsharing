<template>
  <div>
    <h1>Profile {{user.firstname}}</h1>
    {{user}}
  </div>
</template>

<script setup>
import Foodsharing from '@/api/foodsharing';
const foodsharing = new Foodsharing();
const route = useRoute()

definePageMeta({
  middleware: ["auth"]
})

let user;
try {
  user = await foodsharing.get(`user/${route.params.id}/details`)
} catch (e) {
  throw showError(e)
}
</script>
