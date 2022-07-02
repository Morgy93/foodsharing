<template>
  <a
    class="list-group-item list-group-item-action field field--stack"
    :href="$url('store', entry.store.id)"
  >
    <span class="field-container">
      <small
        v-b-tooltip="entry.store.name.length > 30 ? entry.store.name : ''"
        class="field-subline"
        v-html="entry.store.name"
      />
      <span
        class="badge badge-pill d-flex p-1 align-items-center"
        :class="{
          'badge-danger': !entry.confirmed,
          'badge-success': entry.confirmed,
        }"
      >
        <i
          v-b-tooltip="!entry.confirmed ? $i18n('pickup.to_be_confirmed') : ''"
          class="fas"
          :class="{
            'fa-check-circle': entry.confirmed,
            'fa-clock': !entry.confirmed,
          }"
        />
        <span
          v-if="entry.slots.max > 4"
          v-b-tooltip="entry.slots.occupied.map(e=> e.name).join(', ')"
          class="slots"
        >
          {{ entry.slots.occupied.length }} / {{ entry.slots.max }}
        </span>
        <span
          v-else-if="entry.slots.max !== 1"
          class="slots"
        >
          <span
            v-for="(slot, key) in team"
            :key="key"
            class="slot"
          >
            <Avatar
              v-if="slot"
              :key="key"
              v-b-tooltip="slot.name"
              :url="slot.avatar"
              :size="16"
              :auto-scale="false"
              class="slot-user"
              :class="{
                'slot-user--fs-color-danger-500': !entry.confirmed,
                'slot-user--success': entry.confirmed,
              }"
            />
            <i
              v-else
              class="slot-free fas fa-question"
              :class="{
                'slot-free--fs-color-danger-500': !entry.confirmed,
                'slot-free--success': entry.confirmed,
              }"
            />
          </span>
        </span>
      </span>
    </span>
    <h6
      class="field-headline field-headline--big"
      :class="{
        'text-danger': getHourDifferenceToNow(date) < 24,
        'text-black-50': !getHourDifferenceToNow(date) > 24
      }"
    >
      <i class="fas fa-clock mr-2" />
      <span
        v-if="getHourDifferenceToNow(date) < 4"
        v-b-tooltip="dateFormat(date)"
        v-html="dateDistanceInWords(date)"
      />
      <span
        v-else
        v-html="dateFormat(date, 'full-short')"
      />
    </h6>
  </a>
</template>

<script>
// Components
import Avatar from '@/components/Avatar'
// Mixin
import DateFormatterMixin from '@/mixins/DateFormatterMixin'

export default {
  components: {
    Avatar,
  },
  mixins: [DateFormatterMixin],
  props: {
    entry: { type: Object, default: () => {} },
  },
  computed: {
    team () {
      const freeSlots = this.entry.slots.max - this.entry.slots.occupied.length
      return [...this.entry.slots.occupied, ...new Array(freeSlots).fill(null)].reverse()
    },
    date () {
      return new Date(this.entry.date)
    },
  },
}
</script>

<style lang="scss" scoped>
@import '../../../scss/colors.scss';

.slots {
  margin: 0 0.25rem;
  display: inline-flex;
  flex-direction: row-reverse;
}
.slot {
  width: 16px;
  height: 10px;
  display: flex;
  align-items: center;

  &:not(:last-child) {
    margin-left: -0.55rem;
  }
}

::v-deep.slot-user {
  border-radius: 50%;
  overflow: hidden;
  border: 2px currentColor solid;

    &--fs-color-danger-500 {
    border-color: $danger;
  }
  &--success {
    border-color: $success;
  }
}

.slot-free {
  width: 16px;
  height: 16px;
  font-size: .5rem;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px currentColor solid;

  &--fs-color-danger-500 {
    background-color: darken($danger, 10%);
    color: $danger;
  }
  &--success {
    background-color: darken($success, 10%);
    color: $success;
  }
}

</style>
