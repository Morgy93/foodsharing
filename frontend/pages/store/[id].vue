<template>
  <div>
    <a href="/store/overview">Back to Overview</a>

    <h1>{{ data.store.name }}</h1>
    <p>{{ data.store.address }}</p>
    <hr />
    {{ data.store }}
    <hr />
    <textarea name="comment" v-model="data.wallposting" :placeholder="$t('placeholder.wallposting')" />
    <button @click="postWallPosting()">{{ $t('wallpost.send') }}</button>
      <p v-for="post, key of wallposts" :key="key">
        {{ post.text }} - {{ post.author.name}} - {{ $d(new Date(post.createdAt)) }}
        <button v-if="post.author.id === userId" @click="deleteWallPosting(post.id)">{{ $t('wallpost.delete') }}</button>
      </p>
    <hr />
    <ul>
      <li v-for="pickup, key of pickups" :key="key">
        <p>{{ $d(new Date(pickup.date)) }}</p>
        <div>
          <button v-if="!isSignedUp(pickup)" @click="signUpForPickup(pickup.date)">{{ $t('store.pickups.signup')
          }}</button>
          <button v-else @click="signOutOfPickup(pickup.date)">{{ $t('store.pickups.signout') }}</button>
        </div>
        <div v-for="slot, slotkey of getSlots(pickup)" :key="slotkey">
          <PickupUserSlot :slot="slot" />
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import Foodsharing from '@/api/foodsharing';
const foodsharing = new Foodsharing();
const route = useRoute();
const i18n = useI18n();
const storeId = route.params.id;
const userId = useState('user').value.id

definePageMeta({
  middleware: ["auth"]
})

const wallposting = computed(() => {
  return data.wallposting
})

let data = reactive({
  wallposting: '',
  store: {},
  wallposts: [],
  pickups: []
});

const wallposts = computed(() => {
  return data.wallposts
})

const pickups = computed(() => {
  return data.pickups
})


try {
  data.store = (await foodsharing.get(`stores/${storeId}`)).store
  // data.wallposts = await foodsharing.get(`stores/${storeId}/posts`)
  data.wallposts = data.store.notes
  data.pickups = (await foodsharing.get(`stores/${storeId}/pickups`)).pickups
} catch (e) {
  throw showError(e)
}

function getSlots(pickup) {
  let slots = Array.from({ length: pickup.totalSlots });
  slots = slots.map((_, i) => pickup.occupiedSlots[i] || null)
  return slots
}

function isSignedUp(pickup) {
  return pickup.occupiedSlots.find(slot => slot.profile.id === userId) !== undefined
}

async function signUpForPickup(date) {
  try {
    await foodsharing.post(`stores/${storeId}/pickups/${date}/${userId}`)
    data.pickups = (await foodsharing.get(`stores/${storeId}/pickups`)).pickups;
  } catch (e) {
    console.log(e)
  }
}

async function signOutOfPickup(date) {
  try {
    await foodsharing.delete(`stores/${storeId}/pickups/${date}/${userId}`, {
      body: {
        "message": "We need a pick up slot for a new foodsaver",
        "sendKickMessage": false
      }
    })
    data.pickups = (await foodsharing.get(`stores/${storeId}/pickups`)).pickups;
  } catch (e) {
    console.log(e)
  }
}

async function postWallPosting() {
  try {
    const msg = data.wallposting;
    if(msg.trim().length > 0) {
      await foodsharing.post(`stores/${storeId}/posts`, {
        body: {
          text: msg,
        }
      })
      data.wallposts = await foodsharing.get(`stores/${storeId}/posts`)
    }
  } catch (e) {
    console.log(e)
  }
}

async function deleteWallPosting(postId) {
  try {
    await foodsharing.delete(`stores/${storeId}/posts/${postId}`);
    data.wallposts = await foodsharing.get(`stores/${storeId}/posts`)
  } catch (e) {
    console.log(e)
  }
}
</script>


<style scoped>
ul {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
}
</style>
