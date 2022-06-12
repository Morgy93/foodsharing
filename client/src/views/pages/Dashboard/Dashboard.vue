<template>
  <section class="container my-3 my-sm-5">
    <div class="mb-1 mb-sm-3">
      <Information
        v-if="broadcast.body.length > 0"
        type="broadcast"
        tag="broadcast"
        :title="broadcast.title"
        :description="broadcast.body"
        :time="broadcast.last_mod"
        :is-time-based="true"
        :is-closeable="true"
      />
      <Informations
        v-if="errors"
        :all-visible="true"
        :list="errors"
      />
      <Intro :user="user" />
      <Informations
        v-if="informations"
        :list="informations"
      />
      <Information
        v-if="quiz"
        tag="foodsaver.upgrade.ad_fs"
        :title="$i18n('foodsaver.upgrade.ad_fs')"
        :description="quiz.body"
        :is-closeable="quiz.closeable"
        :links="quiz.links"
      />
      <div
        v-if="isFoodsaver()"
        class="filter mt-3"
      >
        <b-dropdown
          id="dropdown-header"
          v-b-tooltip="$i18n('dashboard.options.hide.tooltip')"
          variant="link"
          size="sm"
          class="m-2"
          :text="$i18n('dashboard.options.hide.text')"
          right
        >
          <label
            v-for="([key], index) in Object.entries(visible)"
            :key="index"
            class="dropdown-item d-flex align-items-center"
          >
            <input
              v-model="visible[key]"
              class="mr-2"
              type="checkbox"
            >
            <span style="margin-top:1px">{{ $i18n('dashboard.my.' + key) }}</span>
          </label>
          <div class="dropdown-divider" />
          <button
            class="dropdown-item small d-flex align-items-center"
            @click="resetHiding"
            v-html="$i18n('dashboard.options.reset')"
          />
        </b-dropdown>
        <button
          v-if="viewIsXL"
          v-b-tooltip="$i18n(`dashboard.options.grid_toggle.${state ? 3 : 2}_columns`)"
          class="btn btn-link"
          :disabled="!hasRightColumn"
          @click="toggleState()"
        >
          <i
            class="filter-item--grid fas"
            :class="{
              'fa-th-large': state || !hasRightColumn,
              'fa-th': !state,
            }"
          />
        </button>
      </div>
    </div>
    <div
      class="content grid"
      :class="{ 'grid--2-column': state || !hasRightColumn }"
    >
      <div class="grid-item grid-item--left">
        <PickupList
          v-if="isFoodsaver() &&
            (
              visible.stores && (state || !viewIsXL) ||
              !visible.stores && hasPickups
            )
          "
          :list="pickups"
        />
        <BasketList
          v-if="isFoodsaver()"
          title="basket.nearby"
          :list="baskets"
        />
        <BasketList
          v-if="!isFoodsaver()"
          title="basket.recent"
          :list="baskets"
        />
        <StoreList
          v-if="isFoodsaver() && (state && visible.stores || !viewIsXL && visible.stores)"
          :list="stores"
        />
        <LinkList
          v-if="isFoodsaver() && groups.length > 0 && visible.groups"
          title="dashboard.my.groups"
          type="forum"
          :list="groups"
        />
        <LinkList
          v-if="isFoodsaver() && regions.length > 0 && visible.regions"
          title="dashboard.my.regions"
          type="forum"
          :list="regions"
        />
      </div>
      <div class="grid-item grid-item--middle">
        <EventList
          v-if="isFoodsaver() && visible.events"
          title="dashboard.invitation"
          :list="events.invites"
          options
        />
        <EventList
          v-if="isFoodsaver() && visible.events"
          title="dashboard.event"
          :list="events.accepted"
        />
        <Activity
          :is-foodsaver="isFoodsaver()"
        />
      </div>
      <div
        v-if="isFoodsaver() && !state && hasRightColumn"
        class="grid-item grid-item--right"
      >
        <PickupList
          v-if="isFoodsaver()"
          :list="pickups"
        />
        <StoreList
          v-if="isFoodsaver() && visible.stores"
          :list="stores"
        />
      </div>
    </div>
  </section>
</template>

<script>
import Intro from '@/components/intro/IntroField.vue'
import Information from '@/components/information/InformationField.vue'
import Informations from '@/components/information/InformationWrapper.vue'
import Activity from '@/components/lists/activity/ActivityOverview.vue'
import StoreList from '@/components/lists/store/StoreList.vue'
import PickupList from '@/components/lists/pickup/PickupList.vue'
import EventList from '@/components/lists/event/EventList.vue'
import BasketList from '@/components/lists/basket/BasketList.vue'
import LinkList from '@/components/lists/link/LinkList.vue'

import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import StateTogglerMixin from '@/mixins/StateTogglerMixin'

export default {

  components: {
    Intro,
    Information,
    Informations,
    Activity,
    StoreList,
    PickupList,
    EventList,
    BasketList,
    LinkList,
  },
  mixins: [MediaQueryMixin, StateTogglerMixin],
  props: {
    release: { type: Object, default: () => null },
    broadcast: { type: Object, default: () => null },
    user: { type: Object, default: () => null },
    quiz: { type: Object, default: () => null },
    errors: { type: Array, default: () => null },
    informations: { type: Array, default: () => null },
    stores: { type: Array, default: () => null },
    pickups: { type: Array, default: () => null },
    groups: { type: Array, default: () => null },
    regions: { type: Array, default: () => null },
    events: { type: Object, default: () => ({ accepted: null, invites: null }) },
    baskets: { type: Array, default: () => null },
  },
  data () {
    return {
      stateHasAutoSave: true,
      stateTag: 'dashboard.grid',
      visible: {
        groups: true,
        regions: true,
        events: true,
        stores: true,
      },
    }
  },
  computed: {
    hasStores () {
      return this.stores && this.stores?.length > 0
    },
    hasPickups () {
      return this.pickups && this.pickups?.length > 0
    },
    hasRightColumn () {
      return (this.hasPickups && this.visible.pickups) || (this.hasStores && this.visible.stores)
    },
  },
  watch: {
    visible: {
      handler (newVal) {
        localStorage.setItem('dashboard.visible', JSON.stringify(newVal))
      },
      deep: true,
    },
  },
  mounted () {
    this.visible = JSON.parse(localStorage.getItem('dashboard.visible')) || this.visible
  },
  methods: {
    resetHiding () {
      this.visible = {
        groups: true,
        regions: true,
        events: true,
        stores: true,
      }
    },
    isFoodsaver () {
      return this.user.rolle !== 0
    },
  },
}
</script>

<style lang="scss" scoped>
  .filter {
    display: flex;
    justify-content: end;
  }

  .grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 2fr) minmax(0, 1fr);
    gap: 20px 20px;
    grid-auto-flow: column dense;

    @media (max-width: 1200px) {
      grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
    }

    @media (max-width: 1000px) {
      grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }

    @media (max-width: 769px) {
      grid-template-columns: minmax(0, 1fr);
    }

    &--2-column {
      grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);

      @media (max-width: 1000px) {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
      }

      @media (max-width: 769px) {
        grid-template-columns: minmax(0, 1fr);
      }
    }
  }

  .grid-item--left {
    grid-column: 1;
  }

  .grid-item--middle {
    grid-column: 2;

    @media (max-width: 769px) {
      grid-column: 1;
    }
  }

  .grid-item--right {
    grid-column: 3;

    @media (max-width: 1200px) {
      display: none;
    }
  }
</style>
