// This component should be used if you want to display a new page with a controller that extends FoodsharingController
// it adds the margin to your website, thats it
//

<template>
  <div class="my-5">
    <slot />
  </div>
</template>

<script>
export default {
  name: 'FoodsharingControllerPageWrapper',
}
</script>
