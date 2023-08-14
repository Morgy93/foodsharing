<template>
  <div>
    <div class="field">
      <div class="rounded head ui-widget-header">
        {{ $i18n('group.applications_for', { name: groupName }) }}
      </div>

      <div class="rounded applicants-list ui-widget ui-widget-content">
        <div
          v-if="isLoading"
          class="loader-container mx-auto"
        >
          <i class="fas fa-spinner fa-spin" />
        </div>
        <ul
          v-else
          class="container linklist"
        >
          <li
            v-for="applicant in applicants"
            :key="applicant.id"
          >
            <a
              :href="$url('application', groupId, applicant.id)"
              class="row justify-content-start"
            >
              <Avatar
                :url="applicant.avatar"
                :size="35"
                :sleep-status="applicant.sleepStatus"
                class="mr-2"
              />
              <span class="d-inline avatar-title">{{ applicant.name }}</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { getApplications } from '@/api/applications'
import { pulseError } from '@/script'
import i18n from '@/helper/i18n'
import Avatar from '@/components/Avatar'

export default {
  components: { Avatar },
  props: {
    groupId: { type: Number, required: true },
    groupName: { type: String, required: true },
  },
  data () {
    return {
      isLoading: false,
      applicants: [],
    }
  },
  async mounted () {
    this.isLoading = true

    try {
      this.applicants = await getApplications(this.groupId)
    } catch (err) {
      pulseError(i18n('error_unexpected'))
    }

    this.isLoading = false
  },
}
</script>

<style lang="scss" scoped>
.rounded {
  border-radius: 5px
}

.title {
  font-size: 13px;
  font-weight: normal;
  margin: 0;
  padding: 10px;
}

.applicants-list {
  border: 1px solid var(--fs-border-default);
}

a.row {
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
  border-radius: 4px;
}

.avatar-title {
  text-decoration: none;
  color: var(--fs-color-primary-500);
  font-weight: bold;
}
</style>
