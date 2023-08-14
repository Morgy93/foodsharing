<template>
  <!--
  Default team list order, sorted by number of pickups each:
  - store managers
  - active team members
  - unverified team members
  - jumpers
  Sleeping team members will come last in each of those sections.
  Check `tableSortFunction` (and StoreGateway:getStoreTeam) for details.
  -->
  <div>
    <Container
      :title="title"
      :toggle-visiblity="filteredUser.length > defaultAmount"
      tag="store_team"
      class="bg-white store-team"
      @show-full-list="showFullList"
      @reduce-list="reduceList"
    >
      <div class="text-center mt-2 mb-2">
        <button
          v-if="mayEditStore"
          v-b-tooltip.hover.top
          :title="$i18n(managementModeEnabled ? 'store.sm.managementToggleOff' : 'store.sm.managementToggleOn')"
          class="btn btn-primary btn-sm"
          href="#"
          @click.prevent="toggleManageControls"
        >
          {{ managementModeEnabled ? $i18n('store.sm.buttonManagementToggleOff') : $i18n('store.sm.buttonManagementToggleOn') }}
        </button>
        <button
          class="px-1 d-md-none text-light btn btn-sm"
          href="#"
          @click.prevent="toggleTeamDisplay"
        >
          <i :class="['fas fa-fw', `fa-chevron-${displayMembers ? 'down' : 'left'}`]" />
        </button>
      </div>

      <div
        v-if="managementModeEnabled"
        class="text-center"
      >
        {{ activeMembers }} {{ activeText }} - {{ $i18n('store.of_that') }} {{ jumperCount }} {{ $i18n('store.jumping') }} <br> {{ unverifiedCount }} {{ unverifiedText }}
      </div>
      <!-- preparation for more store-management features -->
      <StoreManagementPanel
        v-if="managementModeEnabled"
        :store-id="storeId"
        :team="team"
        classes="p-2 team-management"
        :region-id="regionId"
      />

      <div class="card-body team-list">
        <b-table
          ref="teamlist"
          :items="filteredList"
          :fields="tableFields"
          :class="{'d-none': !displayMembers}"
          details-td-class="col-actions"
          primary-key="id"
          thead-class="d-none"
          sort-by="ava"
          :busy="isBusy"
          :sort-desc.sync="sortdesc"
          :sort-compare="sortfun"
          show-empty
          sort-null-last
        >
          <template #cell(ava)="data">
            <StoreTeamAvatar :user="data.item" />
          </template>

          <template #cell(info)="data">
            <StoreTeamInfo
              :user="data.item"
              :store-manager-view="managementModeEnabled"
              @toggle-details="toggleActions(data)"
            />
          </template>

          <template #cell(mobinfo)="data">
            <StoreTeamInfotext
              :member="data.item"
              :may-edit-store="mayEditStore"
            />
          </template>

          <template #cell(call)="data">
            <div v-if="data.item.phoneNumberIsValid">
              <b-button
                variant="link"
                class="member-call"
                :href="$url('phone_number', data.item.phoneNumber,true)"
                :disabled="!data.item.phoneNumberIsValid"
              >
                <i class="fas fa-fw fa-phone" />
              </b-button>
              <b-button
                variant="link"
                class="member-call copy-clipboard"
                href="#"
                @click.prevent="copyIntoClipboard(data.item.phoneNumber)"
              >
                <i class="fas fa-fw fa-clone" />
              </b-button>
            </div>
          </template>

          <template #row-details="data">
            <StoreTeamInfotext
              v-if="wXS"
              :member="data.item"
              :may-edit-store="mayEditStore"
              classes="text-center"
            />

            <div class="member-actions">
              <b-button
                v-if="(wXS || wSM)"
                size="sm"
                :href="`/profile/${data.item.id}`"
              >
                <i class="fas fa-fw fa-user" />
                {{ $i18n('pickup.open_profile') }}
              </b-button>

              <b-button
                v-if="data.item.id !== fsId"
                size="sm"
                variant="secondary"
                :block="!(wXS || wSM)"
                @click="openChat(data.item.id)"
              >
                <i class="fas fa-fw fa-comment" />
                {{ $i18n('chat.open_chat') }}
              </b-button>

              <b-button
                v-if="mayEditStore && data.item.isJumper"
                size="sm"
                variant="primary"
                :block="!(wXS || wSM)"
                @click="toggleStandbyState(data.item.id, false)"
              >
                <i class="fas fa-fw fa-clipboard-check" />
                {{ $i18n('store.sm.makeRegularTeamMember') }}
              </b-button>

              <b-button
                v-if="mayEditStore && data.item.isActive && !data.item.isManager"
                size="sm"
                variant="primary"
                :block="!(wXS || wSM)"
                @click="toggleStandbyState(data.item.id, true)"
              >
                <i class="fas fa-fw fa-mug-hot" />
                {{ $i18n('store.sm.makeJumper') }}
              </b-button>

              <b-button
                v-if="managementModeEnabled && mayBecomeManager(data.item)"
                size="sm"
                variant="warning"
                :block="!(wXS || wSM)"
                @click="promoteToManager(data.item.id)"
              >
                <i class="fas fa-fw fa-cog" />
                {{ $i18n('store.sm.promoteToManager') }}
              </b-button>

              <b-button
                v-if="managementModeEnabled && data.item.isManager"
                size="sm"
                variant="outline-primary"
                :block="!(wXS || wSM)"
                @click="demoteAsManager(data.item.id, data.item.name)"
              >
                <i class="fas fa-fw fa-cog" />
                {{ $i18n('store.sm.demoteAsManager') }}
              </b-button>

              <b-button
                v-if="mayRemoveFromStore(data.item)"
                size="sm"
                variant="danger"
                :block="!(wXS || wSM)"
                @click="openDeleteModal(data.item)"
              >
                <i class="fas fa-fw fa-user-times" />
                {{ $i18n('store.sm.removeFromTeam') }}
              </b-button>
            </div>
          </template>
        </b-table>
      </div>
    </Container>
    <b-modal
      id="deleteModal"
      ref="deleteModal"
      :title="$i18n('store.sm.reallyRemove', { name: selectedDataItem ? selectedDataItem.name : '' })"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.yes_i_am_sure')"
      cancel-variant="primary"
      ok-variant="outline-danger"
      @ok="removeFromTeam(selectedDataItem ? selectedDataItem.id : null, selectedDataItem ? selectedDataItem.name : '')"
    >
      {{ $i18n('really_delete') }}
    </b-modal>
  </div>
</template>

<script>
import {
  demoteAsStoreManager, promoteToStoreManager,
  moveMemberToStandbyTeam, moveMemberToRegularTeam, removeStoreMember,
} from '@/api/stores'
import phoneNumber from '@/helper/phone-numbers'
import { chat, pulseSuccess, pulseError } from '@/script'
import MediaQueryMixin from '@/mixins/MediaQueryMixin'

import StoreManagementPanel from '@/components/Stores/StoreTeam/StoreManagementPanel.vue'
import StoreTeamAvatar from '@/components/Stores/StoreTeam/StoreTeamAvatar.vue'
import StoreTeamInfo from '@/components/Stores/StoreTeam/StoreTeamInfo.vue'
import StoreTeamInfotext from '@/components/Stores/StoreTeam/StoreTeamInfotext.vue'
import StoreData from '@/stores/stores'
import Container from '@/components/Container/Container.vue'
import ListToggleMixin from '@/mixins/ContainerToggleMixin'

export default {
  components: { StoreManagementPanel, StoreTeamAvatar, StoreTeamInfo, StoreTeamInfotext, Container },
  mixins: [MediaQueryMixin, ListToggleMixin],
  props: {
    fsId: { type: Number, required: true },
    mayEditStore: { type: Boolean, default: false },
    team: { type: Array, required: true },
    storeId: { type: Number, required: true },
    storeTitle: { type: String, default: '' },
    regionId: { type: Number, required: true },
  },
  data () {
    return {
      foodsaver: this.team?.map(fs => this.foodsaverData(fs)),
      sortfun: this.tableSortFunction,
      sortdesc: true,
      managementModeEnabled: false,
      displayMembers: true,
      isBusy: false,
      selectedDataItem: null,
    }
  },
  computed: {
    filteredUser () {
      const data = this.foodsaver
      this.setList(data)
      return data
    },
    tableFields () {
      const fields = [
        { key: 'ava', class: 'col-ava', sortable: true },
        { key: 'info', class: 'col-info' },
      ]
      if (this.wSM) {
        fields.push({ key: 'mobinfo', class: 'col-mobinfo' })
      }
      if (this.wXS || this.wSM) {
        fields.push({ key: 'call', class: 'col-call' })
      }
      return fields
    },
    jumperCount () {
      const isJumperType = 2
      return this.team.filter(member => member.team_active === isJumperType).length
    },
    unverifiedCount () {
      const unverifiedState = 0
      return this.team.filter(member => member.verified === unverifiedState).length
    },
    activeMembers () {
      return this.team.length - this.jumperCount - this.unverifiedCount
    },
    unverifiedText () {
      return this.unverifiedCount === 1 ? this.$i18n('store.unverified_member') : this.$i18n('store.unverified_members')
    },
    activeText () {
      return this.activeMembers === 1 ? this.$i18n('store.one_active') : this.$i18n('store.active')
    },
    title () {
      return `${this.$i18n('store.team_container')} (${this.activeMembers} ${this.activeText}) `
    },
  },
  watch: {
    team () {
      this.foodsaver = this.team?.map(fs => this.foodsaverData(fs))
    },
  },
  methods: {
    toggleManageControls () {
      this.sortfun = this.managementModeEnabled ? this.tableSortFunction : this.pickupSortFunction
      this.managementModeEnabled = !this.managementModeEnabled
    },
    toggleTeamDisplay () {
      this.displayMembers = !this.displayMembers
    },
    canCopy () {
      return !!navigator.clipboard
    },
    copyIntoClipboard (text) {
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
          pulseSuccess(this.$i18n('pickup.copiedNumber', { number: text }))
        })
      }
    },
    mayRemoveFromStore (user) {
      if (user.isManager) return false
      if (user.id === this.fsId) return true
      return this.mayEditStore
    },
    openDeleteModal (dataItem) {
      this.$refs.deleteModal.show(dataItem)
    },
    mayBecomeManager (user) {
      if (!user.mayManage) return false
      if (user.isJumper) return false
      return !user.isManager
    },
    toggleActions (row) {
      const wasOpen = row.detailsShowing
      this.foodsaver.forEach((item) => {
        if (item._showDetails) {
          // Firefox has some funny ideas about focus handling, so we must all suffer
          this.$root.$emit('bv::hide::tooltip', 'member-' + item.id)
          // close previously open action list
          this.$set(item, '_showDetails', false)
        }
      })
      if (!wasOpen) {
        row.toggleDetails()
        this.selectedDataItem = row.item
      }
    },
    openChat (fsId) {
      chat(fsId)
    },
    async toggleStandbyState (fsId, newStatusIsStandby) {
      this.isBusy = true
      try {
        if (newStatusIsStandby) {
          await moveMemberToStandbyTeam(this.storeId, fsId)
        } else {
          await moveMemberToRegularTeam(this.storeId, fsId)
        }
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        const fs = this.foodsaver[index]
        fs.isWaiting = newStatusIsStandby
        fs.isJumper = newStatusIsStandby
        fs.isActive = !newStatusIsStandby
        fs._showDetails = false
        this.foodsaver[index] = fs
      }
      this.isBusy = false
    },
    async promoteToManager (fsId) {
      if (!fsId) {
        return
      }
      this.isBusy = true
      try {
        await promoteToStoreManager(this.storeId, fsId)
      } catch (e) {
        if (e.code === 422) {
          pulseError(this.$i18n('store.sm.promoteToManagerNotPossible'))
        } else {
          pulseError(this.$i18n('error_unexpected'))
        }
        this.isBusy = false
        return
      }
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        const fs = this.foodsaver[index]
        fs.isManager = true
        fs._rowVariant = 'warning'
        fs._showDetails = false
        this.foodsaver[index] = fs
      }
      this.isBusy = false
    },
    async demoteAsManager (fsId, fsName) {
      if (!fsId) {
        return
      }
      if (!confirm(this.$i18n('store.sm.reallyDemote', { name: fsName }))) {
        return
      }
      this.isBusy = true
      try {
        await demoteAsStoreManager(this.storeId, fsId)
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      const index = this.team.findIndex(fs => fs.id === fsId)
      if (index >= 0) {
        const fs = this.foodsaver[index]
        fs.isManager = false
        fs._rowVariant = ''
        fs._showDetails = false
        this.foodsaver[index] = fs
      }
      this.isBusy = false
    },
    /* eslint-disable brace-style */
    pickupSortFunction (a, b, key, directionDesc) {
      const direction = directionDesc ? 1 : -1
      // ORDER BY
      // isManager (verantwortlich) DESC
      if (a.isManager !== b.isManager) { return direction * (a.isManager - b.isManager) }
      // lastPickup (last_fetch) DESC
      if (a.lastPickup && b.lastPickup) {
        if (a.lastPickup.getTime() === b.lastPickup.getTime()) return 0
        return (a.lastPickup > b.lastPickup ? 1 : -1) * direction
      }
      else if (a.lastPickup) { return direction }
      else if (b.lastPickup) { return -1 * direction }
      // joinDate (add_date) DESC
      if (a.joinDate && b.joinDate) {
        if (a.joinDate.getTime() === b.joinDate.getTime()) return 0
        return (a.joinDate > b.joinDate ? 1 : -1) * direction
      }
      else if (a.joinDate) { return direction }
      else if (b.joinDate) { return -1 * direction }
      // name ASC
      return -1 * direction * a.name.localeCompare(b.name)
    },
    /* eslint-enable brace-style */
    tableSortFunction (a, b, key, directionDesc) {
      const direction = directionDesc ? 1 : -1
      // ORDER BY
      // isManager (verantwortlich) DESC
      if (a.isManager !== b.isManager) { return direction * (a.isManager - b.isManager) }
      // isJumper (team_active == MembershipStatus::JUMPER) ASC
      if (a.isJumper !== b.isJumper) { return -1 * direction * (a.isJumper - b.isJumper) }
      // isVerified (verified == 1) DESC
      if (a.isVerified !== b.isVerified) { return direction * (a.isVerified - b.isVerified) }
      // sleepStatus (sleep_status) ASC
      if (a.sleepStatus !== b.sleepStatus) { return -1 * direction * (a.sleepStatus - b.sleepStatus) }
      // fetchCount (stat_fetchcount) DESC
      if (a.fetchCount !== b.fetchCount) { return direction * (a.fetchCount - b.fetchCount) }
      // lastPickup (last_fetch) DESC
      if (a.lastPickup && b.lastPickup) {
        if (a.lastPickup.getTime() === b.lastPickup.getTime()) return 0
        return (a.lastPickup > b.lastPickup ? 1 : -1) * direction
      }
      // joinDate (add_date) DESC
      if (a.joinDate && b.joinDate) {
        if (a.joinDate.getTime() === b.joinDate.getTime()) return 0
        return (a.joinDate > b.joinDate ? 1 : -1) * direction
      }
      // name ASC
      return -1 * direction * a.name.localeCompare(b.name)
    },
    foodsaverData (fs) {
      if (!fs) {
        return {}
      }
      const validPhoneNumber = phoneNumber.callableNumber(fs.handy || fs.telefon, true)
      return {
        id: fs.id,
        isActive: fs.team_active === 1, // MembershipStatus::MEMBER
        isJumper: fs.team_active === 2, // MembershipStatus::JUMPER
        isManager: !!fs.verantwortlich,
        _rowVariant: fs.verantwortlich ? 'warning' : '',
        isVerified: fs.verified === 1,
        mayManage: fs.quiz_rolle >= 2, // Role::STORE_MANAGER
        // mayAmb: fs.quiz_rolle >= 3, // Role::AMBASSADOR
        avatar: fs.photo,
        isWaiting: fs.team_active === 2 || fs.verified < 1, // MembershipStatus::JUMPER or unverified
        sleepStatus: fs.sleep_status,
        name: fs.name,
        phoneNumber: validPhoneNumber,
        phoneNumberIsValid: !!validPhoneNumber,
        joinDate: fs.add_date ? new Date(fs.add_date * 1000) : null, // unix time
        lastPickup: fs.last_fetch ? new Date(fs.last_fetch * 1000) : null, // unix time
        fetchCount: fs.stat_fetchcount,
      }
    },
    async removeFromTeam (fsId, fsName) {
      if (!fsId) {
        return
      }
      this.isBusy = true
      try {
        await removeStoreMember(this.storeId, fsId)
        await StoreData.mutations.loadStoreMember(this.storeId)
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
        this.isBusy = false
        return
      }
      this.isBusy = false
    },
  },
}
</script>

<style lang="scss" scoped>
.store-team .team-management {
  border-bottom: 2px solid var(--fs-color-warning-500);
}

.store-team .team-list {
  padding: 0;
}

.store-team ::v-deep table {
  display: flex;
  flex-direction: row;
  margin-bottom: 0;

  thead, tbody {
    width: 100%;

    tr {
      display: flex;
      border-bottom: 1px solid var(--fs-border-default);

      &.b-table-details {
        justify-content: center;
      }

      &.table-warning {
        border-bottom-width: 2px;
        border-bottom-color: var(--fs-color-warning-500);
        padding-bottom: 1px;
      }

      &:last-child,
      &.b-table-has-details {
        border-bottom-width: 0;
      }

      td {
        border-top: 0;
      }
    }
  }

  tr td {
    padding: 3px;
    border-top-color: var(--fs-border-default);
    vertical-align: middle;
    cursor: default;
    display: inline-block;

    &.col-actions {
      padding: 0;
    }

    &.col-ava {
      position: relative;
      align-self: center;
    }

    &.col-info {
      flex-grow: 1;
    }

    &.col-mobinfo {
      padding: 0 10px;
      text-align: right;
    }

    &.col-call {
      .member-call {
        padding: 10px;
        align-self: center;
        color: var(--fs-color-secondary-500);

        &.copy-clipboard { opacity: 0.7; }

        &:hover {
          background-color: var(--fs-color-secondary-500);
          color: var(--fs-color-light);
        }
        &:focus {
          outline: 2px solid var(--fs-color-secondary-500);
        }
        &:disabled {
          color: var(--fs-color-primary-300);
        }
      }
    }

    .member-actions {
      padding: 5px 0;

      .btn {
        margin-bottom: 5px;
      }
    }
  }
}
</style>
