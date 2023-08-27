<template>
  <!-- eslint-disable vue/max-attributes-per-line -->
  <b-modal
    ref="modal_report_request"
    :title="$i18n('profile.report.title', { name: foodSaverName })"
    header-class="d-flex"
    content-class="pr-3 pt-3"
  >
    <div id="report_request" class="popbox m-2">
      <div
        v-if="!isReportButtonEnabled"
      >
        <div>
          <h3>{{ $i18n('profile.report.oldReportButton') }}</h3>
          <hr>
          <p>
            {{ $i18n('profile.report.oldReportButtonTextPart1') }} <br>
            {{ $i18n('profile.report.oldReportButtonTextPart2') }} <br>
          </p>
          <p>
            {{ $i18n('profile.report.oldReportButtonTextPart3') }}
            <a href="https://foodsharing.de/?page=blog&amp;sub=read&amp;id=255">{{ $i18n('profile.report.inthisblog') }}</a>
          </p>
        </div>
      </div>
      <b-alert
        v-else-if="!reporterHasReportGroup"
        variant="info" show
      >
        <div>
          {{ $i18n('profile.report.reporterHasNoReportGroup') }}
        </div>
      </b-alert>
      <b-alert
        v-else-if="isReportedIdReportAdmin && !hasArbitrationGroup && !isReporterIdReportAdmin"
        variant="info" show
      >
        <div>
          {{ $i18n('profile.report.reportedAdminNoArbitration', { name: foodSaverName }) }}
        </div>
      </b-alert>
      <b-alert
        v-else-if="isReporterIdReportAdmin && !hasArbitrationGroup"
        variant="info" show
      >
        <div>
          {{ $i18n('profile.report.reporterAdminNoArbitration') }}
        </div>
      </b-alert>
      <b-alert
        v-else-if="isReporterIdReportAdmin && isReportedIdArbitrationAdmin"
        variant="info" show
      >
        <div>
          {{ $i18n('profile.report.repAdminAgainstArbAdmin') }}
        </div>
      </b-alert>
      <b-alert
        v-else-if="isReporterIdArbitrationAdmin && isReportedIdReportAdmin"
        variant="info" show
      >
        <div>
          {{ $i18n('profile.report.arbAdminAgainstRepAdmin') }}
        </div>
      </b-alert>
      <b-alert
        v-else-if="!hasReportGroup"
        variant="info" show
      >
        <div>
          {{ $i18n('profile.report.noReportGroup') }}
        </div>
      </b-alert>
      <template
        v-else
      >
        <b-alert variant="info" show>
          <div>{{ $i18n('profile.report.info') }}</div>
        </b-alert>
        <div>{{ $i18n('profile.report.kindofreport') }}</div>
        <b-form-select
          v-model="reportReason"
          :options="reportReasonOptions"
          name="reportReason"
          class="mb-2"
        />
        <b-form-select
          v-model="storeList"
          :options="storeListOptions"
          class="mb-2"
          align-v="stretch"
        />
        <b-form-textarea
          v-model="reportText"
          class="mb-2"
          max-rows="8"
          size="sm"
        />
        <b-alert variant="info" show>
          <div>{{ $i18n('profile.report.mail') }}</div>
          <a :href="$url('mailto_mail_foodsharing_network', mailboxName)">
            {{ $url('mail_foodsharing_network', mailboxName) }}
          </a>
        </b-alert>
      </template>
    </div>
    <template #modal-footer>
      <button
        class="btn btn-secondary cancel"
        @click="$refs.modal_report_request.hide()"
        v-text="$i18n('button.cancel')"
      />
      <button
        type="button"
        class="btn btn-primary"
        :disabled="sendButtonDisabled"
        @click="trySendReport"
        v-text="$i18n('profile.report.send')"
      />
    </template>
  </b-modal>
</template>

<script>
import { addReport } from '@/api/report'
import { pulseError, pulseInfo } from '@/script'
import i18n from '@/helper/i18n'

export default {
  props: {
    foodSaverName: { type: String, required: true },
    reportedId: { type: Number, required: true },
    reporterId: { type: Number, required: true },
    storeListOptions: { type: Array, default: () => { return [] } },
    isReportedIdReportAdmin: { type: Boolean, required: true },
    hasReportGroup: { type: Boolean, required: true },
    hasArbitrationGroup: { type: Boolean, required: true },
    isReporterIdReportAdmin: { type: Boolean, required: true },
    isReportedIdArbitrationAdmin: { type: Boolean, required: true },
    isReporterIdArbitrationAdmin: { type: Boolean, required: true },
    isReportButtonEnabled: { type: Boolean, required: true },
    reporterHasReportGroup: { type: Boolean, required: true },
    mailboxName: { type: String, required: true },
    reasonOptionSettings: { type: Number, required: false, default: 1 },
    reasonOptionSettingsOther: { type: Boolean, required: false, default: false },
  },
  data () {
    const reportReasonOptionsValues = []
    if (this.reasonOptionSettings === 2) {
      reportReasonOptionsValues.push(
        { value: '1', text: this.$i18n('profile.report.report_b1_1') },
        { value: '2', text: this.$i18n('profile.report.report_b1_2') },
        { value: '20', text: this.$i18n('profile.report.report_b1_3') },
        { value: '21', text: this.$i18n('profile.report.report_b1_4') },
        { value: '22', text: this.$i18n('profile.report.report_b1_5') },
        { value: '23', text: this.$i18n('profile.report.report_b2') },
        { value: '24', text: this.$i18n('profile.report.report_b3_1') },
        { value: '25', text: this.$i18n('profile.report.report_b3_2') },
        { value: '26', text: this.$i18n('profile.report.report_b4_1') },
        { value: '27', text: this.$i18n('profile.report.report_b4_2') },
        { value: '28', text: this.$i18n('profile.report.report_b5') },
        { value: '29', text: this.$i18n('profile.report.report_b6_1') },
        { value: '30', text: this.$i18n('profile.report.report_b6_2') },
        { value: '31', text: this.$i18n('profile.report.report_b7') },
        { value: '32', text: this.$i18n('profile.report.report_b8') },
        { value: '33', text: this.$i18n('profile.report.report_b9') },
        { value: '34', text: this.$i18n('profile.report.report_b10') },
        { value: '35', text: this.$i18n('profile.report.report_b11') },
        { value: '36', text: this.$i18n('profile.report.report_b12') },
        { value: '37', text: this.$i18n('profile.report.report_b13') },
      )
    } else {
      reportReasonOptionsValues.push(
        { value: '1', text: this.$i18n('profile.report.late') },
        { value: '2', text: this.$i18n('profile.report.noshow') },
        { value: '10', text: this.$i18n('profile.report.cancellation') },
        { value: '15', text: this.$i18n('profile.report.sells') },
      )
    }
    if (this.reasonOptionSettingsOther) {
      reportReasonOptionsValues.push(
        { value: '99', text: this.$i18n('profile.report.other') },
      )
    }
    return {
      reportText: '',
      storeList: null,
      reportReasonOptions: reportReasonOptionsValues,
      reportReason: [],
    }
  },
  computed: {
    sendButtonDisabled () {
      return this.reportText.length <= 0 || this.reportReason === null
    },
  },
  methods: {
    async trySendReport () {
      const message = this.reportText.trim()
      if (!message) return
      try {
        const selectedReasons = this.reportReason.map(optionValue => {
          const selectedOption = this.reportReasonOptions.find(option => option.value === optionValue)
          return selectedOption ? selectedOption.text : ''
        })
        const sortedSelectedReasons = selectedReasons.sort() // Sortiere das Array
        const selectedReasonsText = sortedSelectedReasons.join(', ')
        await addReport(this.reportedId, this.reporterId, 1, selectedReasonsText, message, this.storeList)
        pulseInfo(i18n('profile.report.sent'))
        this.reportReason = null
        this.storeList = null
        this.reportText = ''
      } catch (err) {
        pulseError(i18n('error_unexpected'))
      }

      this.$refs.modal_report_request.hide()
    },
    show () {
      this.$refs.modal_report_request.show()
    },
  },
}
</script>
<style lang="scss" scoped>
#mediation_request {
  min-width: 50vw;
  max-width: 250px;
}
</style>
