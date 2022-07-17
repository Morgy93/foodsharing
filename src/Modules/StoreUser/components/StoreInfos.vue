<template>
  <div class="bootstrap store-desc w-100">
    <div class="card rounded mb-2">
      <div
        v-if="storeTitle != null"
        class="card-header text-white bg-primary d-flex justify-content-between"
        @click.prevent="toggleInfoDisplay"
      >
        {{ storeTitle }}

        <b-link
          class="px-1 text-light"
          @click.prevent.stop="toggleInfoDisplay"
        >
          <i :class="['fas fa-fw', `fa-chevron-${displayInfos ? 'down' : 'left'}`]" />
        </b-link>
      </div>

      <div
        v-show="displayInfos"
        class="card-body bg-white p-2"
      >
        <div
          id="inputAdress"
          class="desc-block mb-1 py-1"
        >
          <div class="desc-block-title mb-2 py-1">
            {{ $i18n('store.address') }}
          </div>
          <div>
            {{ street }} <br>
            {{ postcode }} {{ city }}
          </div>
        </div>
        <div
          id="inputParticularities"
          class="desc-block mb-1 py-1"
        >
          <div class="desc-block-title mb-2 py-1">
            {{ $i18n('store.particularities') }}
          </div>
          <Markdown :source="particularitiesDescription" />
        </div>
        <div
          id="inputAverageCollectionQuantity"
          class="desc-block mb-1 py-1"
        >
          <div class="desc-block-title mb-2 py-1">
            {{ $i18n('store.average_collection_quantity') }}
          </div>
          <div>
            {{ collectionQuantity }}
          </div>
        </div>
        <div
          id="inputAttribution"
          class="desc-block mb-1 py-1"
        >
          <div class="desc-block-title mb-2 py-1">
            {{ $i18n('store.attribution') }}
          </div>
          <span v-if="allowedToMentionInPublic">{{ $i18n('store.may_referred_to_in_public') }}</span>
          <span v-else>{{ $i18n('store.may_not_referred_to_in_public') }}</span>
        </div>
        <div
          v-if="lastFetchDate !== null"
          id="inputMyLastPickup"
          class="desc-block mb-1 py-1"
        >
          <div class="desc-block-title mb-2 py-1">
            {{ $i18n('store.my_last_pickup') }}
          </div>
          <span>
            {{ $dateFormatter.dateBasic(lastFetchDate) }}
          </span>
          ({{ $dateFormatter.relativeTime(lastFetchDate) }})
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Markdown from '@/components/Markdown/Markdown'

export default {
  components: { Markdown },
  props: {
    particularitiesDescription: {
      type: String,
      default: '',
    },
    collectionQuantity: {
      type: String,
      default: '',
    },
    storeTitle: {
      type: String,
      default: null,
    },
    street: {
      type: String,
      default: '',
    },
    housenumber: {
      type: String,
      default: '',
    },
    postcode: {
      type: String,
      default: '',
    },
    city: {
      type: String,
      default: '',
    },
    lastFetchDate: {
      type: [Date, String],
      default: null,
    },
    press: {
      type: Number,
      default: 0,
    },
  },
  data () {
    return {
      displayInfos: true,
    }
  },
  computed: {
    allowedToMentionInPublic () {
      return this.press === 1
    },
  },
  methods: {
    toggleInfoDisplay () {
      this.displayInfos = !this.displayInfos
    },
  },
}
</script>

<style lang="scss" scoped>
.store-desc {
  display: inline-block;
  font-size: 0.875rem;

  div, p, ul, ol, th, td, label {
    font-size: inherit;
  }

  .desc-block {
    max-width: 100%;
    /* Global fallback */
    overflow-wrap: break-word;
    /* Safari / Edge compat: */
    word-break: break-word;
    /* Desired behavior: */
    overflow-wrap: anywhere;

    ::v-deep .markdown {
      div, ul, ol, th, td, label {
        margin-bottom: 0;
        font-size: inherit;
      }
    }
  }

  .desc-block-title {
    background-color: var(--fs-color-info-200);
    border-radius: var(--border-radius);
    color: var(--fs-color-info-600);
    font-weight: bolder;
    text-align: center;
  }
}
</style>
