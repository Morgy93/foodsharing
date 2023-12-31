<template>
  <section
    v-if="user"
    class="container my-3 my-sm-5"
  >
    <div class="mb-1 mb-sm-3">
      <Broadcast
        v-if="broadcast"
        :entry="broadcast"
      />
      <Release v-if="!isBeta" />
      <ErrorContainer />
      <Intro />
      <InformationsContainer />
      <Quiz
        v-if="quiz"
        tag="foodsaver.upgrade.ad_fs"
        :title="$i18n('foodsaver.upgrade.ad_fs')"
        :description="quiz.body"
        :is-closeable="quiz.closeable"
        :links="quiz.links"
      />
      <div
        v-if="isFoodsaver"
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
            v-text="$i18n('dashboard.options.reset')"
          />
        </b-dropdown>
        <button
          v-if="viewIsXL"
          v-b-tooltip="$i18n(`dashboard.options.grid_toggle.${state ? 3 : 2}_columns`)"
          class="btn btn-link btn-icon"
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
          <span
            class="sr-only"
            v-text="$i18n(`dashboard.options.grid_toggle.${state ? 3 : 2}_columns`)"
          />
        </button>
      </div>
    </div>
    <div
      class="content grid"
      :class="{ 'grid--2-column': !isFoodsaver || state || !hasRightColumn }"
    >
      <div class="grid-item grid-item--left">
        <PickupContainer v-if="isFoodsaver && (visible.stores && (state || !viewIsXL) || !visible.stores && hasPickups)" />
        <BasketContainer />
        <StoreContainer v-if="isFoodsaver && (state && visible.stores || !viewIsXL && visible.stores)" />
        <ManagingStoreContainer v-if="isFoodsaver && (state && visible.managing_stores || !viewIsXL && visible.managing_stores)" />
        <JumpingStoreContainer v-if="isFoodsaver && (state && visible.jumping_stores || !viewIsXL && visible.jumping_stores)" />
        <GroupContainer v-if="isFoodsaver && visible.groups" />
        <RegionContainer v-if="isFoodsaver && visible.regions" />
      </div>
      <div class="grid-item grid-item--middle">
        <PollContainer v-if="visible.polls" />
        <EventContainer
          v-if="isFoodsaver && visible.events"
          title="dashboard.invitation"
          options
        />
        <EventContainer
          v-if="isFoodsaver && visible.events"
          title="dashboard.event"
        />
        <ActivityContainer />
      </div>
      <div
        v-if="isFoodsaver && !state && hasRightColumn"
        class="grid-item grid-item--right"
      >
        <PickupContainer v-if="isFoodsaver" />
        <ManagingStoreContainer v-if="isFoodsaver && visible.managing_stores" />
        <JumpingStoreContainer v-if="isFoodsaver && visible.jumping_stores" />
        <StoreContainer v-if="isFoodsaver && visible.stores" />
      </div>
    </div>
  </section>
</template>

<script>
// Stores
import DataStores from '@/stores/stores.js'
import DataPickups from '@/stores/pickups.js'
import DataBaskets from '@/stores/baskets.js'
import DataUser, { mutations } from '@/stores/user.js'
import DataEvents from '@/stores/events.js'
import DataBroadcast from '@/stores/broadcast.js'
// Components
import Broadcast from '@/components/Banners/Broadcast/BroadcastField.vue'
import Intro from '@/components/Banners/Intro/IntroField.vue'
import Release from '@/components/Banners/Release/ReleaseField.vue'
import Quiz from '@/components/Banners/Quiz/QuizField.vue'
import ErrorContainer from '@/components/Banners/Errors/ErrorContainer.vue'
import InformationsContainer from '@/components/Banners/Informations/InformationContainer.vue'
import ActivityContainer from '@/components/Container/activity/ActivityOverview.vue'
import StoreContainer from '@/components/Container/store/StoreContainer.vue'
import ManagingStoreContainer from '@/components/Container/store/ManagingStoreContainer.vue'
import JumpingStoreContainer from '@/components/Container/store/JumpingStoreContainer.vue'
import PickupContainer from '@/components/Container/pickup/PickupContainer.vue'
import EventContainer from '@/components/Container/event/EventContainer.vue'
import BasketContainer from '@/components/Container/basket/BasketContainer.vue'
import RegionContainer from '@/components/Container/region/RegionContainer.vue'
import GroupContainer from '@/components/Container/group/GroupContainer.vue'
import PollContainer from '@/components/Container/poll/PollContainer.vue'
// Mixins
import MediaQueryMixin from '@/mixins/MediaQueryMixin'
import StateTogglerMixin from '@/mixins/StateTogglerMixin'
import RouteAndDeviceCheckMixin from '@/mixins/RouteAndDeviceCheckMixin'

export default {
  components: {
    Broadcast,
    Intro,
    Release,
    Quiz,
    ErrorContainer,
    InformationsContainer,
    ActivityContainer,
    StoreContainer,
    ManagingStoreContainer,
    JumpingStoreContainer,
    PickupContainer,
    EventContainer,
    BasketContainer,
    RegionContainer,
    GroupContainer,
    PollContainer,
  },
  mixins: [MediaQueryMixin, StateTogglerMixin, RouteAndDeviceCheckMixin],
  props: {
    quiz: { type: Object, default: () => null },
    events: { type: Object, default: () => ({ accepted: null, invites: null }) },
  },
  data () {
    return {
      stateHasAutoSave: true,
      stateTag: 'dashboard.grid',
      visible: {
        groups: true,
        regions: true,
        events: true,
        managing_stores: true,
        jumping_stores: true,
        stores: true,
        polls: true,
      },
    }
  },
  computed: {
    user: () => DataUser.getters.getUser(),
    isLoggedIn: () => DataUser.getters.isLoggedIn(),
    isFoodsaver: () => DataUser.getters.isFoodsaver(),
    hasStores: () => DataStores.getters.hasStores(),
    hasPickups: () => DataPickups.getters.getRegistered(),
    isStoresVisible () {
      return this.visible.stores || this.visible.managing_stores || this.visible.jumping_stores
    },
    hasRightColumn () {
      return (this.hasPickups && this.visible.pickups) || (this.hasStores && this.isStoresVisible)
    },
    getLocations: () => DataUser.getters.getLocations(),
    broadcast: () => DataBroadcast.getters.getBroadcastMessage(),
  },
  watch: {
    visible: {
      handler (newVal) {
        localStorage.setItem('dashboard.visible', JSON.stringify(newVal))
      },
      deep: true,
    },
    isFoodsaver: {
      async handler (newVal) {
        if (newVal) {
          await DataPickups.mutations.fetchRegistered()
          // TODO: NO APIS :(
          DataEvents.mutations.setAccepted(this.events.accepted)
          DataEvents.mutations.setInvited(this.events.invites)
        }
      },
      immediate: true,
      deep: true,
    },
    getLocations: {
      async handler (coords) {
        if (coords.lat && coords.lon) {
          await DataBaskets.mutations.fetchNearby(coords)
        }
      },
      immediate: true,
      deep: true,
    },
  },
  async mounted () {
    this.visible = JSON.parse(localStorage.getItem('dashboard.visible')) || this.visible
    await DataBroadcast.mutations.fetch()
    await mutations.fetchDetails()
  },
  methods: {
    resetHiding () {
      this.visible = {
        groups: true,
        regions: true,
        events: true,
        managing_stores: true,
        jumping_stores: true,
        stores: true,
        polls: true,
      }
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
