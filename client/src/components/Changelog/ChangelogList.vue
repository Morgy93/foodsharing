<template>
  <section>
    <p>Here you can see our changes since 2023.</p>
    <BTable
      striped
      hover
      responsive
      small
      bordered
      :items="changelogEntries"
      :fields="headlines"
    >
      <template #cell(types_of_change)="data">
        <ul>
          <li
            v-for="change in data.item.types_of_change"
            :key="change"
          >
            {{ change }}
          </li>
        </ul>
      </template>

      <template #cell(authors)="data">
        <span
          v-for="author in data.item.authors"
          :key="author"
        >
          <a
            :href="getGitLabProfilUrlForUser(author)"
            target="_blank"
          >{{ author }}</a>&nbsp;
        </span>
      </template>
    </BTable>
  </section>
</template>

<script>
import { getChangelog } from '@/api/changelog'
import { BTable } from 'bootstrap-vue'

export default {
  name: 'ChangelogList',
  components: {
    BTable,
  },
  data () {
    return {
      headlines: [
        {
          key: 'types_of_change',
          label: 'Type of change',
        },
        {
          key: 'description',
        },
        {
          key: 'authors',
          label: 'Contributors',
        },
        {
          key: 'release',
        },
        {
          key: 'added_at',
          sortable: true,
        },
      ],
      changelogEntries: [],
    }
  },
  async mounted () {
    const changelog = await getChangelog()
    this.updateDisplayedChangelogEntries(changelog.changelog)
  },
  methods: {
    updateDisplayedChangelogEntries (changelog) {
      const releases = Object.values(changelog)

      releases.forEach(release => {
        const releaseInformation = release.release

        const changes = Object.values(release.changes)
        changes.forEach(change => {
          change.release = releaseInformation.name
          change.added_at = this.$dateFormatter.date(change.added_at)
          this.changelogEntries.push(change)
        })
      })
    },
    getGitLabProfilUrlForUser (username) {
      return `https://gitlab.com/${username}/`
    },
  },
}
</script>

<style scoped>

</style>
