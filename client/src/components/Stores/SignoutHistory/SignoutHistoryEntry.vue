<template>
  <div>
    <b>{{ $i18n('pickup.signout_history.slot') }} {{ $dateFormatter.dateTime(pickupSlot.pickup_date_object) }}</b>
    <ul>
      <li
        v-for="signout in pickupSlot.signouts"
        :key="signout.information.performed_at"
      >
        <span v-if="signout.information.action_id === 12">
          <a :href="signout.information.affected_foodsaver.profil_url">
            {{ signout.information.affected_foodsaver.name }}
          </a> <i class="far fa-clock" /> {{ $dateFormatter.dateTime(new Date(signout.information.performed_at)) }}
        </span>

        <span v-if="signout.information.action_id === 13">
          <a :href="signout.information.performed_foodsaver.profil_url">
            {{ signout.information.performed_foodsaver.name }}
          </a>
          {{ $i18n('pickup.signout_history.signout_via_responsible_person.via') }}
          <a :href="signout.information.affected_foodsaver.profil_url">
            {{ signout.information.affected_foodsaver.name }}
          </a>
          {{
            $i18n('pickup.signout_history.signout_via_responsible_person.remove_reason', { reason: signout.information.reason })
          }} <i class="far fa-clock" /> {{ $dateFormatter.dateTime(new Date(signout.information.performed_at)) }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'SignoutHistoryEntry',

  props: {
    pickupSlot: { type: Object, default: null },
  },
}
</script>

<style scoped>

</style>
