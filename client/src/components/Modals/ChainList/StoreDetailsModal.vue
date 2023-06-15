<template>
  <b-modal
    ref="details-modal"
    :title="$i18n('chain.detailsmodal.title', { chain: selectedChain.name })"
    modal-class="bootstrap"
    centered
    size="lg"
    hide-header-close
    ok-only
    ok-variant="secondary"
  >
    <dt v-if="storeList !== null && storeList.length === 0">
      {{ $i18n('chain.detailsmodal.nostores') }}
    </dt>
    <div v-else>
      <dt>{{ $i18n('chain.detailsmodal.stores') }}</dt>
      <i
        v-if="storeList === null"
        class="fas fa-spinner fa-spin"
      />
      <dd v-else>
        <span
          v-for="(store, index) in storeList"
          :key="store.id"
        >
          <span v-if="index">, </span>
          <a :href="$url('store', store.id)">{{ store.name }}</a>
        </span>
      </dd>
    </div>
  </b-modal>
</template>

<script>
export default {
  props: {
    storeList: {
      validator: prop => prop === null || prop.constructor === Array,
      required: true,
    },
  },
  data () {
    return {
      selectedChain: {},
    }
  },
  methods: {
    show (selectedChain) {
      this.selectedChain = selectedChain
      this.$refs['details-modal'].show()
    },
  },
}
</script>
