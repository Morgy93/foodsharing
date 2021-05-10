<!-- eslint-disable vue/max-attributes-per-line -->
<template>
  <b-modal
    ref="basketPopupModal"
    :title="$i18n('basket.by', { name: basket!=null ? basket.creator.name : '' } )"
    modal-class="bootstrap"
    header-class="d-flex"
    content-class="pr-3 pt-3"
    size="sm"
    scrollable
    centered
    hide-backdrop
  >
    <div
      v-if="basket==null"
      class="loader-container mx-auto"
    >
      <b-img
        center
        src="/img/469.gif"
      />
    </div>
    <div
      v-else
    >
      {{ basket }}
    </div>

    <template #modal-footer>
      <div class="w-100">
        <b-button
          variant="secondary"
          size="sm"
          class="float-right"
          @click="goToBasket"
        >
          {{ $i18n('basket.go') }}
        </b-button>
      </div>
    </template>
  </b-modal>
</template>

<script>

import { BModal, BButton } from 'bootstrap-vue'
import { pulseError } from '@/script'
import i18n from '@/i18n'
import { getBasket } from '@/api/baskets'

export default {
  components: { BModal, BButton },
  props: {
  },
  data () {
    return {
      basketId: 0,
      basket: null,
      loading: false,
    }
  },
  methods: {
    async load (basketId) {
      this.basketId = basketId
      this.$refs.basketPopupModal.show()

      this.loading = true
      try {
        this.basket = (await getBasket(basketId)).basket
      } catch (e) {
        pulseError(i18n('error_unexpected'))
        this.$refs.basketPopupModal.hide()
      }
      this.loading = false
    },
    goToBasket () {
      document.location.href = this.$url('basket', this.basketId)
    },
  },
}
</script>

<style lang="scss" scoped>
</style>
