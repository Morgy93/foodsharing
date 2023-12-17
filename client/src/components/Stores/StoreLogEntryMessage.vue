<template>
  <div>
    <div v-if="action.action_id === STORE_LOG_ACTION.CREATE_OR_UPDATE_REGULAR_PICKUP">
      <p
        v-for="entry in actionContent"
        :key="entry.weekday"
        v-html="actionText(action, entry)"
      />
    </div>

    <span
      v-else
      v-html="actionText(action)"
    />
  </div>
</template>

<script>
import { STORE_LOG_ACTION } from '@/stores/stores'

const ACTION_TYPES_WITH_OPTIONAL_REASON = [13]

export default {
  props: {
    action: { type: Object, required: true },
  },
  data () {
    return {
      actionContent: [],
      weekdays: [
        { value: 1, text: this.$i18n('date.monday') },
        { value: 2, text: this.$i18n('date.tuesday') },
        { value: 3, text: this.$i18n('date.wednesday') },
        { value: 4, text: this.$i18n('date.thursday') },
        { value: 5, text: this.$i18n('date.friday') },
        { value: 6, text: this.$i18n('date.saturday') },
        { value: 0, text: this.$i18n('date.sunday') },
      ],
    }
  },
  computed: {
    STORE_LOG_ACTION () {
      return STORE_LOG_ACTION
    },
  },
  mounted () {
    if (this.action.content.trim() !== '') {
      try {
        this.actionContent = JSON.parse(this.action.content.trim())
      } catch (error) {
        console.error('Error parsing JSON:', error)
      }
    }
  },
  methods: {
    actionText (action, entry) {
      const params = {
        actor: `<a href="${this.$url('profile', action.acting_foodsaver.id)}">${action.acting_foodsaver.name}</a>`,
        target: `<a href="${this.$url('profile', action.affected_foodsaver?.id)}">${action.affected_foodsaver?.name}</a>`,
        date: this.$dateFormatter.format(action.date_reference),
      }

      if (action.action_id === STORE_LOG_ACTION.CREATE_OR_UPDATE_SINGLE_PICKUP_SLOT) {
        const actionContent = JSON.parse(action.content)
        params.description = actionContent.description
        params.totalSlots = actionContent.totalSlots
      }

      if (action.action_id === STORE_LOG_ACTION.CREATE_OR_UPDATE_REGULAR_PICKUP) {
        const weekdayObject = this.weekdays.find(day => day.value === entry.weekday)
        params.weekday = weekdayObject ? weekdayObject.text : entry.weekday
        params.startTimeOfPickup = entry.startTimeOfPickup
        params.description = entry.description ? entry.description : this.$i18n('store.log.no_description')
        params.maxCountOfSlots = entry.maxCountOfSlots
      }

      const reason = (action.reason && ACTION_TYPES_WITH_OPTIONAL_REASON.includes(action.action_id)) ? '_with_reason' : ''
      return this.$i18n(`store.log.message.${action.action_id}${reason}`, params)
    },
  },
}
</script>
