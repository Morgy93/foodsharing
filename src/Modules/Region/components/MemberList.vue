<template>
  <div class="card mb-3 rounded">
    <div
      v-if="isWorkGroup"
      class="card-header text-white bg-primary"
    >
      {{ $i18n('memberlist.header_for_workgroup', {bezirk: regionName}) }}
      <span>
        {{ $i18n('memberlist.some_in_all', {some: membersFiltered.length, all: memberList.length}) }}
      </span>
    </div>
    <div
      v-else
      class="card-header text-white bg-primary"
    >
      {{ $i18n('memberlist.header_for_district', {bezirk: regionName}) }}
      <span>
        {{ $i18n('memberlist.some_in_all', {some: membersFiltered.length, all: memberList.length}) }}
      </span>
    </div>

    <div
      v-if="memberList.length"
      class="card-body p-0"
    >
      <div class="row">
        <div
          v-if="isWorkGroup && mayEditMembers"
          class="user-search-input"
        >
          <user-search-input
            id="new-foodsaver-search"
            class="m-1"
            :placeholder="$i18n('search.user_search.placeholder')"
            button-icon="fa-user-plus"
            :button-tooltip="$i18n('group.member_list.add_member')"
            :filter="notContainsMember"
            @user-selected="addNewTeamMember"
          />
        </div>
        <div
          v-if="mayEditMembers"
          class="filter-role"
        >
          <b-form-select
            v-model="filterRole"
            :options="roleOptions"
            size="sm"
          />
        </div>
        <div
          v-if="mayEditMembers"
          class="filter-activity-toggle"
        >
          <b-form-checkbox
            v-model="filterLastActivity"
            switch
            size="sm"
          >
            {{ $i18n('group.filter_by_last_activity') }}
          </b-form-checkbox>
        </div>
        <div
          v-if="mayEditMembers"
          class="filter-activity-chooser"
        >
          <b-form-spinbutton
            v-model="lastActivityFilterMonths"
            min="1"
            max="12"
            size="sm"
            :disabled="!filterLastActivity"
          />
        </div>
      </div>
      <div
        class="form-row"
      >
        <div
          class="filter-for-label"
        >
          <label class=" col-form-label col-form-label-sm foo">
            {{ $i18n('list.filter_for') }}
          </label>
        </div>
        <div
          class="filter-for-form"
        >
          <input
            v-model="filterText"
            type="text"
            class="form-control form-control-sm"
            placeholder="Name"
          >
        </div>
        <div
          class="filter-for-delete"
        >
          <button
            v-b-tooltip.hover
            :title="$i18n('button.clear_filter')"
            type="button"
            class="btn btn-sm"
            @click="clearFilter"
          >
            <i class="fas fa-times" />
          </button>
        </div>
      </div>
    </div>

    <b-table
      :fields="filteredFields"
      :items="membersFilteredSorted"
      :current-page="currentPage"
      :per-page="perPage"
      :sort-compare="compare"
      :busy="isBusy"
      small
      hover
      responsive
      class="foto-table"
    >
      <template #cell(imageUrl)="row">
        <div>
          <avatar
            :url="row.item.avatar"
            :is-sleeping="row.item.sleepStatus"
            :size="50"
          />
        </div>
      </template>
      <template #cell(userName)="row">
        <a
          :href="$url('profile', row.item.id)"
          :title="row.item.id"
        >
          {{ row.item.name }}
        </a>
      </template>
      <template #cell(lastActivity)="row">
        {{ $dateFormatter.format(row.item.lastActivity, {
          day: 'numeric',
          month: 'numeric',
          year: 'numeric',
        }) }}
      </template>
      <template #cell(role)="row">
        {{ $i18n('terminology.role.' + row.item.role) }}
      </template>
      <template
        v-if="mayRemoveAdminOrAmbassador"
        #cell(removeAdminButton)="row"
      >
        <b-button
          v-if="rowItemisAdminOrAmbassadorOfRegion(row.item)"
          v-b-tooltip="$i18n(isWorkGroup ? 'group.member_list.remove_admin_title' : 'group.member_list.remove_ambassador_title')"
          size="sm"
          variant="danger"
          :disabled="isBusy"
          @click="showRemoveAdminMemberConfirmation(row.item.id, row.item.name)"
        >
          <i class="fas fa-fw fa-user-slash" />
        </b-button>
      </template>
      <template
        v-if="maySetAdminOrAmbassador && managementModeEnabled"
        #cell(setAdminButton)="row"
      >
        <b-button
          v-if="rowItemNotqualUserid(userId,row.item.id) && roleCheckForRegionAndWorkGroup(isWorkGroup,row.item.role) && !rowItemisAdminOrAmbassadorOfRegion(row.item)"
          v-b-tooltip.left="$i18n(isWorkGroup ? 'group.member_list.set_admin_title' : 'group.member_list.set_ambassador_title')"
          size="sm"
          variant="warning"
          :disabled="isBusy"
          @click="showSetAdminMemberConfirmation(row.item.id, row.item.name)"
        >
          <i class="fas fa-fw fa-user-graduate" />
        </b-button>
      </template>
      <template
        v-if="mayEditMembers"
        #cell(removeButton)="row"
      >
        <b-button
          v-if="rowItemNotqualUserid(userId,row.item.id) && !rowItemisAdminOrAmbassadorOfRegion(row.item)"
          v-b-tooltip="$i18n('group.member_list.remove_title')"
          size="sm"
          variant="danger"
          :disabled="isBusy"
          @click="showRemoveMemberConfirmation(row.item.id, row.item.name)"
        >
          <i class="fas fa-fw fa-user-times" />
        </b-button>
      </template>
    </b-table>
    <div class="float-right p-1 pr-3">
      <b-pagination
        v-model="currentPage"
        :total-rows="membersFiltered.length"
        :per-page="perPage"
        class="my-0"
      />
    </div>
  </div>
</template>

<script>
import { optimizedCompare } from '@/utils'
import { BButton, BFormSelect, BTable, BPagination, VBTooltip } from 'bootstrap-vue'
import { addMember } from '@/api/groups'
import { removeMember, listRegionMembers, setAdminOrAmbassador, removeAdminOrAmbassador } from '@/api/regions'
import { hideLoader, pulseError, showLoader } from '@/script'
import i18n from '@/helper/i18n'
import UserSearchInput from '@/components/UserSearchInput'
import Avatar from '@/components/Avatar'

export default {
  components: { Avatar, BButton, BFormSelect, BTable, BPagination, UserSearchInput },
  directives: { VBTooltip },
  props: {
    userId: { type: Number, default: null },
    groupId: { type: Number, required: true },
    regionName: {
      type: String,
      default: '',
    },
    isWorkGroup: {
      type: Boolean,
      default: false,
    },
    mayEditMembers: { type: Boolean, default: false },
    maySetAdminOrAmbassador: { type: Boolean, default: false },
    mayRemoveAdminOrAmbassador: { type: Boolean, default: false },
  },
  data () {
    return {
      currentPage: 1,
      perPage: 20,
      filterText: '',
      filterRole: null,
      filterLastActivity: false,
      lastActivityFilterMonths: 6,
      memberList: [],
      isBusy: false,
      managementModeEnabled: false,
      roleOptions: [
        { value: null, text: i18n('group.role_name') },
        { value: 1, text: i18n('terminology.role.1') },
        { value: 2, text: i18n('terminology.role.2') },
        { value: 3, text: i18n('terminology.role.3') },
        { value: 4, text: i18n('terminology.role.4') },
      ],
    }
  },
  computed: {
    dateBeforeMonths () {
      return new Date(new Date().getTime() - this.lastActivityFilterMonths * 30 * 24 * 60 * 60 * 1000)
    },
    membersFiltered () {
      if (!this.filterText.trim() && this.filterRole === null && !this.filterLastActivity) {
        return this.memberList
      }
      const filterText = this.filterText ? this.filterText.toLowerCase() : null
      return this.memberList.filter((member) => {
        return (
          (!filterText || (member.name.toLowerCase().indexOf(filterText) !== -1)) &&
          (this.filterRole === null || (member.role === this.filterRole)) &&
          (!this.filterLastActivity || (Date.parse(member.lastActivity) > this.dateBeforeMonths))
        )
      })
    },
    membersFilteredSorted () {
      // sorts the member list alphabetically
      const copy = this.membersFiltered
      return copy.sort(function (a, b) {
        return a.name.localeCompare(b.name)
      })
    },
    filteredFields () {
      const columns = [
        {
          key: 'imageUrl',
          sortable: false,
          label: '',
          class: 'foto-column',
        }, {
          key: 'userName',
          label: this.$i18n('group.name'),
          sortable: false,
          class: 'align-middle',
        },
      ]
      if (this.mayEditMembers) {
        columns.push({
          key: 'lastActivity',
          label: this.$i18n('group.last_activity'),
          sortable: true,
          class: 'align-middle',
        }, {
          key: 'role',
          label: this.$i18n('group.role_name'),
          sortable: true,
          class: 'align-middle',
        })
      }
      columns.push(
        {
          key: 'setAdminButton',
          label: '',
          sortable: false,
          class: 'button-column',
        }, {
          key: 'removeAdminButton',
          label: '',
          sortable: false,
          class: 'button-column',
        }, {
          key: 'removeButton',
          label: '',
          sortable: false,
          class: 'button-column',
        },
      )
      return columns
    },
  },
  async mounted () {
    // fetch the member list from the server
    showLoader()
    this.isBusy = true
    try {
      this.memberList = await listRegionMembers(this.groupId)
    } catch (e) {
      pulseError(i18n('error_unexpected'))
    }
    this.isBusy = false
    hideLoader()
  },
  methods: {
    compare: optimizedCompare,

    clearFilter () {
      this.filterStatus = null
      this.filterText = ''
    },
    rowItemisAdminOrAmbassadorOfRegion (value) {
      return value.isAdminOrAmbassadorOfRegion === true
    },
    rowItemNotqualUserid (user, value) {
      return user !== value
    },
    roleCheckForRegionAndWorkGroup (isGroup, itemRole) {
      return isGroup ? itemRole >= 2 : itemRole === 3
    },
    async tryRemoveAdminMember (memberId) {
      showLoader()
      this.isBusy = true
      try {
        await removeAdminOrAmbassador(this.groupId, memberId)
        const index = this.memberList.findIndex(member => member.id === memberId)
        if (index >= 0) {
          this.memberList[index].isAdminOrAmbassadorOfRegion = false
        }
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async showRemoveAdminMemberConfirmation (memberId, memberName) {
      const remove = await this.$bvModal.msgBoxConfirm(i18n(this.isWorkGroup ? 'group.member_list.remove_admin_text' : 'group.member_list.remove_ambassador_text', { name: memberName, id: memberId }), {
        modalClass: 'bootstrap',
        title: i18n(this.isWorkGroup ? 'group.member_list.remove_admin_title' : 'group.member_list.remove_ambassador_title'),
        cancelTitle: i18n('button.cancel'),
        okTitle: i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (remove) {
        this.tryRemoveAdminMember(memberId)
      }
    },
    async trySetAdminMember (memberId) {
      showLoader()
      this.isBusy = true
      try {
        await setAdminOrAmbassador(this.groupId, memberId)
        const index = this.memberList.findIndex(member => member.id === memberId)
        if (index >= 0) {
          this.memberList[index].isAdminOrAmbassadorOfRegion = true
        }
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async showSetAdminMemberConfirmation (memberId, memberName) {
      const remove = await this.$bvModal.msgBoxConfirm(i18n(this.isWorkGroup ? 'group.member_list.set_admin_text' : 'group.member_list.set_ambassador_text', { name: memberName, id: memberId }), {
        modalClass: 'bootstrap',
        title: i18n(this.isWorkGroup ? 'group.member_list.set_admin_title' : 'group.member_list.set_ambassador_title'),
        cancelTitle: i18n('button.cancel'),
        okTitle: i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (remove) {
        this.trySetAdminMember(memberId)
      }
    },
    async tryRemoveMember (memberId) {
      showLoader()
      this.isBusy = true
      try {
        await removeMember(this.groupId, memberId)
        const index = this.memberList.findIndex(member => member.id === memberId)
        if (index >= 0) {
          this.memberList.splice(index, 1)
        }
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async showRemoveMemberConfirmation (memberId, memberName) {
      const remove = await this.$bvModal.msgBoxConfirm(i18n('group.member_list.remove_text', { name: memberName, id: memberId }), {
        modalClass: 'bootstrap',
        title: i18n('group.member_list.remove_title'),
        cancelTitle: i18n('button.cancel'),
        okTitle: i18n('yes'),
        headerClass: 'd-flex',
        contentClass: 'pr-3 pt-3',
      })
      if (remove) {
        this.tryRemoveMember(memberId)
      }
    },
    toggleManageControls () {
      this.managementModeEnabled = !this.managementModeEnabled
    },
    containsMember (memberId) {
      return this.memberList.some(member => member.id === memberId)
    },
    notContainsMember (memberId) {
      return !this.containsMember(memberId)
    },
    async addNewTeamMember (userId) {
      showLoader()
      this.isBusy = true
      try {
        const addedUser = await addMember(this.groupId, userId)

        // the backend doesn't care if the user was already in the group, so we have to check here
        if (!this.containsMember(userId)) {
          this.memberList.push(addedUser)
        }
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
  },
}
</script>

<style lang="scss" scoped>
.foto-table ::v-deep .foto-column {
  width: 60px;
}

.foto-table ::v-deep .button-column {
  width: 50px;
  vertical-align: middle;
  text-align: center;
}

.filter-activity-toggle {
  margin:0.5rem;
  margin-left:1.5rem;
  @media (min-width: 375px) {
    flex-basis: 50%;
    order: 1;
  }

  @media (min-width: 1200px) {
    flex-basis: 25%;
    order: 1;
  }
}

.filter-activity-chooser {
  margin:0.5rem;
  flex-basis: 25%;
  order: 2;
}

.filter-role {
  margin:0.5rem;
  margin-left:1.5rem;
  @media (min-width: 375px) {
    flex-basis: 85%;
    order: 3;
  }

  @media (min-width: 1200px) {
    flex-basis: 40%;
    order: 3;
  }
}

.filter-for-label {
  @media (min-width: 375px) {
    flex-basis: 5%;
    order: 1;
    margin-left:1rem;
  }

  @media (min-width: 1200px) {
    flex-basis: 10%;
    order: 1;
    margin-top:0.7rem
  }
}

  .filter-for-form {
  @media (min-width: 375px) {
    flex-basis: 60%;
    order: 2;
    margin-left:1rem;
    margin-top:0.5rem
  }

  @media (min-width: 1200px) {
    flex-basis: 75%;
    order: 2;
    margin:0.5rem
  }
}

  .filter-for-delete {
  @media (min-width: 375px) {
    flex-basis: 2%;
    order: 3;
    margin:0.5rem;
  }

  @media (min-width: 1200px) {
    flex-basis: 5%;
    order: 3;
    margin:0.7rem;
  }
}

.user-search-input {
  @media (min-width: 375px) {
    flex-basis: 88%;
    order: 1;
    margin:0.5rem;
    margin-left:1.2rem
  }

  @media (min-width: 1200px) {
    flex-basis: 90%;
    order: 1;
    margin:0.7rem;
    margin-left:1.5rem
  }
}
</style>
