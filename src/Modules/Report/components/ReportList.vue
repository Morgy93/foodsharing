<template>
  <div class="card mb-3 rounded">
    <div class="card-header text-white bg-primary">
      {{ $i18n('reports.all_reports') }} <span v-if="reports.length">
        ({{ reports.length }})
      </span>
    </div>
    <div
      card-body
      v-html="$i18n('profile.report.readup')"
    />
    <div
      v-if="reports.length"
      class="card-body p-0"
    >
      <b-table
        :fields="fields"
        :items="reports"
        :current-page="currentPage"
        :per-page="perPage"
        responsive
      >
        <template
          slot="avatar"
          slot-scope="row"
        >
          <div class="avatars">
            <a :href="`/profile/${row.item.fs_id}`">
              <Avatar
                :url="row.item.fs_photo"
                :is-sleeping="0"
                :size="35"
              />
            </a>
            <a :href="`/profile/${row.item.rp_id}`">
              <Avatar
                :url="row.item.rp_photo"
                :is-sleeping="0"
                :size="35"
              />
            </a>
          </div>
        </template>

        <template
          #cell(actions)="row"
        >
          <b-button
            size="sm"
            @click.stop="row.toggleDetails"
          >
            {{ row.detailsShowing ? 'x' : 'Details' }}
          </b-button>
        </template>
        <template
          #row-details="row"
        >
          <div class="report">
            <p><strong>{{ $i18n('reports.report_id') }}</strong>: {{ row.item.id }}</p>
            <p><strong>{{ $i18n('reports.time') }}</strong>: {{ row.item.time }}</p>
            <p v-if="row.item.betrieb_id !== 0">
              <strong>{{ $i18n('reports.store') }}</strong>: <a :href="`/?page=fsbetrieb&id=${row.item.betrieb_id}`">
                {{ row.item.betrieb_name }}</a> ({{ row.item.betrieb_id }})
            </p>
            <p>
              <strong>{{ $i18n('reports.about') }}</strong>:<a :href="`/profile/${row.item.fs_id}`">
                {{ row.item.fs_name }} {{ row.item.fs_nachname }}
              </a> ({{ row.item.fs_id }})
            </p>
            <p>
              <strong>{{ $i18n('reports.from') }}</strong>:<a :href="`/profile/${row.item.rp_id}`">
                {{ row.item.rp_name }} {{ row.item.rp_nachname }}
              </a> ({{ row.item.rp_id }})
            </p>
            <p><strong>{{ $i18n('reports.reason') }}</strong>: {{ row.item.tvalue }}</p>
            <p><strong>{{ $i18n('reports.message') }}</strong>: {{ row.item.msg }}</p>
          </div>
        </template>
      </b-table>
      <div class="float-right p-1 pr-3">
        <b-pagination
          v-model="currentPage"
          :total-rows="reports.length"
          :per-page="perPage"
          class="my-0"
        />
      </div>
    </div>
    <div
      v-else
      class="card-body"
    >
      {{ $i18n('reports.no_reports_fallback') }}
    </div>
  </div>
</template>

<script>

import { BTable, BPagination, BButton } from 'bootstrap-vue'
import * as api from '@/api/report'

import Avatar from '@/components/Avatar'

export default {
  components: { Avatar, BTable, BPagination, BButton },
  props: {
    regionId: {
      type: String,
      default: null,
    },
    regionName: {
      type: String,
      default: '',
    },
  },
  data () {
    return {
      currentPage: 1,
      perPage: 50,
      reports: [],
      fields: [
        {
          key: 'avatar',

          label: '',
        },
        {
          key: 'fs_stadt',
          label: this.$i18n('reports.city'),
          sortable: true,
        },
        {
          key: 'time',
          label: this.$i18n('reports.time'),
          sortable: true,
        },
        {
          key: 'fs_name',
          label: this.$i18n('reports.about_first_name'),
          sortable: true,
        },
        {
          key: 'fs_nachname',
          label: this.$i18n('reports.about_last_name'),
          sortable: true,
        },
        {
          key: 'fs_email',
          label: this.$i18n('reports.about_email'),
          sortable: true,
        },
        {
          key: 'rp_name',
          label: this.$i18n('reports.from_first_name'),
          sortable: true,
        },
        {
          key: 'rp_nachname',
          label: this.$i18n('reports.from_last_name'),
          sortable: true,
        },
        {
          key: 'b_name',
          label: this.$i18n('reports.region'),
          sortable: true,
        },
        {
          key: 'actions',
          label: '',
        },
      ],
    }
  },
  async created () {
    const reports = await api.getReportsByRegion(this.regionId)
    Object.assign(this, {
      reports,
    })
  },
}
</script>
<style>
  .avatars {
    display: flex;
  }
  .avatars div {
    margin-right: 5px;
  }

</style>
