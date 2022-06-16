<template>
  <div class="store-desc bootstrap rounded list-group mb-2">
    <div
      class="list-group-item py-2 text-white font-weight-bold bg-primary d-flex justify-content-between"
      v-html="$i18n('status')"
    />
    <b-form-select
      v-model="form.teamStatus"
      :options="teamStatusOptions"
      @change="change('teamStatus')"
    />
  </div>
</template>

<script>
import _ from 'underscore'
import i18n from '@/i18n'
import { updateStore } from '@/api/stores'

const BAD_TO_GOOD = {
  team_status: 'teamStatus',
}
const GOOD_TO_BAD = _.invert(BAD_TO_GOOD)

const rawToast = {
  toaster: 'b-toaster-top-right',
  noCloseButton: true,
  autoHideDelay: 3000, // milliseconds
  solid: false, // transparent
  isStatus: true, // accessibility
}

export default {
  props: {
    storeData: { type: Object, required: true },
  },
  data: function () {
    return {
      form: _.mapObject(GOOD_TO_BAD, (val, key) => { return this.storeData[val] }),
      newFormValue: this.storeData,
    }
  },
  computed: {
    teamStatusOptions () {
      return [
        { value: null, text: i18n('storeedit.dropdownDefault'), disabled: true },
        { value: 0, text: i18n('storeedit.fetch.teamStatus0') },
        { value: 1, text: i18n('storeedit.fetch.teamStatus1') },
        { value: 2, text: i18n('storeedit.fetch.teamStatus2') },
      ]
    },
  },
  methods: {
    async change (field, evt = null) {
      const storeId = this.storeData.id
      const newValue = evt || this.form[field]
      const dbField = GOOD_TO_BAD[field]
      if (dbField) {
        try {
          await updateStore(storeId, dbField, newValue)
          this.successToast()
          this.newFormValue[dbField] = newValue
          //   this.storeData[dbField] = newValue
        } catch (err) {
          console.error(`Could not update field ${field} to '${newValue}'.`, err.message)
          this.burnedToast()
          // reset vue data to old, non-updated value
          const oldValue = this.storeData[dbField]
          this.form[field] = oldValue
        }
      } else {
        console.warn('Tried updating unknown store field:', field)
      }
    },
    successToast () {
      this.$bvToast.toast(' ', Object.assign({
        title: i18n('saved'),
        variant: 'success',
      }, rawToast))
    },
    burnedToast () {
      this.$bvToast.toast(' ', Object.assign({
        title: i18n('oops'),
        variant: 'danger',
      }, rawToast))
    },
  },
}
</script>
<style lang="scss" scoped>
.list-group-item:not(:last-child) {
  border-bottom: 0;
}
</style>
