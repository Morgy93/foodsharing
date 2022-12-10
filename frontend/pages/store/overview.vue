<template>
  <div>
    <h1>Store OVERVIEW</h1>
    <table>
      <thead>
        <th>Name</th>
        <th>Verantwortlich</th>
        <th>Status</th>
      </thead>
      <tbody v-for="store, key of stores" :key="key">
        <td><a :href="`/store/${store.id}`">{{store.name}}</a></td>
        <td>{{store.isManaging}}</td>
        <td>{{$t(`status.${store.pickupStatus}`, 0) }}</td>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import Foodsharing from '@/api/foodsharing';
const foodsharing = new Foodsharing();

definePageMeta({
  middleware: ["auth"]
})

let stores;
try {
  stores = await foodsharing.get(`user/current/stores`)
} catch (e) {
  throw showError(e)
}
</script>
