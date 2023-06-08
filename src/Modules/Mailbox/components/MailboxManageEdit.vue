<template>
  <div>
    {{ mailbox }}
  </div>
</template>

<script>

export default {
  props: {
    mailbox: { type: Array, required: true },
  },
}
</script>
