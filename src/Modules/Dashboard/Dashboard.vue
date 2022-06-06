<template>
  <section>
    <div class="mb-1 mb-sm-5">
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
    </div>
    <div class="content row">
      <div
        class="col order-2 order-xl-1 mb-3"
        :class="{
          'col-md-6': !isFoodsaver(),
          'col-md-6 col-xl-3': isFoodsaver(),
        }"
      >
        <BasketList
          v-if="baskets.nearby.length > 0"
          title="basket.nearby"
          :list="baskets.nearby"
        />
        <BasketList
          v-else
          title="basket.recent"
          :list="baskets.recent"
        />
        <LinkList
          title="dashboard.my.groups"
          type="forum"
          :list="groups"
        />
        <LinkList
          title="dashboard.my.regions"
          type="forum"
          :list="regions"
        />
      </div>

      <div
        class="col order-3 order-xl-2"
        :class="{
          'col-md-6': !isFoodsaver(),
          'col-md-12 col-xl-6 ': isFoodsaver(),
        }"
      >
        <EventList
          title="dashboard.invitations"
          :list="events.invites"
        />
        <EventList
          :count="events.accepted.length"
          :list="events.accepted"
        />
        <Activity :is-foodsaver="isFoodsaver()" />
      </div>
      <div
        v-if="isFoodsaver() && (pickups || stores)"
        class="col col-md-6 col-xl-3 order-1 order-xl-3 mb-3"
      >
        <PickupList :list="pickups" />
        <StoreList :list="stores" />
      </div>
    </div>
  </section>
</template>

<script>
import Intro from '../__components/intro/IntroField.vue'
import Activity from '../__components/activity/ActivityOverview.vue'
import Information from '../__components/information/InformationField.vue'
import Informations from '../__components/information/InformationWrapper.vue'
import StoreList from '../__components/lists/StoreList.vue'
import PickupList from '../__components/lists/PickupList.vue'
import EventList from '../__components/lists/event/EventList.vue'
import BasketList from '../__components/lists/BasketList.vue'
import LinkList from '../__components/lists/LinkList.vue'

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
  props: {
    release: { type: Object, default: () => {} },
    broadcast: { type: Object, default: () => {} },
    user: { type: Object, default: () => {} },
    quiz: { type: Object, default: () => {} },
    errors: { type: Array, default: () => [] },
    informations: { type: Array, default: () => [] },
    stores: { type: Array, default: () => [] },
    pickups: { type: Array, default: () => [] },
    groups: { type: Array, default: () => [] },
    regions: { type: Array, default: () => [] },
    events: { type: Object, default: () => ({ accepted: [], invites: [] }) },
    baskets: { type: Object, default: () => ({ recent: [], nearby: [] }) },
  },
  methods: {
    isFoodsaver () {
      return this.user.rolle !== 0
    },
  },
}
</script>
