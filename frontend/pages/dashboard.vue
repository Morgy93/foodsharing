<template>
  <div>
    <p>{{ $t('foodsharer', {count: counter}) }}</p>
    <button @click="increase">
      +
    </button>

    <button @click="clear">
      clear
    </button>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: ["auth"]
})

const counter = useCount._raw();

function increase() {
  useCount.increase();
}

function clear() {
  useCount.clear();
}


</script>
