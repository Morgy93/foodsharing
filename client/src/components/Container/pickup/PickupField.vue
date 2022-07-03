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
        class="pickup-status badge badge-pill d-flex p-1 align-items-center"
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
          v-b-tooltip="entry.slots.occupied.map(e=> e.name).join(', ')"
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
              :url="slot.avatar"
              :size="24"
              :auto-scale="false"
              class="slot-user"
              :class="{
                'slot-user--need-confirm': !entry.confirmed,
                'slot-user--success': entry.confirmed,
              }"
            />
            <i
              v-else
              class="slot-free fas fa-question"
              :class="{
                'slot-free--need-confirm': !entry.confirmed,
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
        'text-danger': getHourDifferenceToNow(date) < 4
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

.pickup-status {
  font-size: 1rem;
}
.slots {
  margin: 0 0.25rem;
  display: inline-flex;
  flex-direction: row-reverse;
}

$size: 1.25rem;
.slot {
  width: $size;
  height: 1rem;
  display: flex;
  align-items: center;

  &:not(:last-child) {
    margin-left: -0.55rem;
  }
}

::v-deep.slot-user {
  width: $size;
  height: $size;
  border-radius: 50%;
  overflow: hidden;
  border: 2px currentColor solid;

  &--need-confirm {
    border-color: $danger;
  }
  &--success {
    border-color: $success;
  }
}

.slot-free {
  width: $size;
  height: $size;
  font-size: .5rem;
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px currentColor solid;

  &--need-confirm {
    background-color: var(--fs-color-danger-300);
    color: var(--fs-color-danger-500);
  }
  &--success {
    background-color: var(--fs-color-success-300);
    color: var(--fs-color-success-500);
  }
}

</style>
