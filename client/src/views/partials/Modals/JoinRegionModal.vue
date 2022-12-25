<template>
  <b-modal
    id="joinRegionModal"
    ref="joinRegionModal"
    modal-class="testing-region-join"
    :title="$i18n('join_region.headline')"
    :cancel-title="$i18n('globals.close')"
    :ok-title="$i18n('globals.save')"

    @show="showModal"
    @hidden="resetModal"
    @ok="joinRegion"
  >
    <div class="description">
      <p v-html="$i18n('join_region.description', {href: $url('wiki_create_region'), mail: $url('mailto_mail_foodsharing_network', 'welcome')})" />
    </div>
    <hr>
    <div class="selector">
      <select
        v-model="selected[0]"
        class="testing-region-join-select custom-select"
      >
        <option
          :value="0"
          v-html="$i18n('globals.select')"
        />
        <option
          v-for="(entry, key) in base"
          :key="key"
          :value="entry.id"
          v-html="entry.name"
        />
      </select>
      <select
        v-for="(region, listId) in regionsList"
        :key="listId"
        v-model="selected[listId + 1]"
        class="custom-select"
      >
        <option
          :value="null"
          v-text="$i18n('globals.select')"
        />
        <option
          v-for="(entry, key) in region.list"
          :key="key"
          :value="entry.id"
          v-html="entry.name"
        />
      </select>
    </div>
    <div
      v-if="regionIsInValid && selectedRegionType"
      class="alert alert-danger d-flex align-items-center"
    >
      <i class="icon icon--big fas fa-exclamation-triangle" />
      <span
        v-if="selectedRegionType === 5"
        v-html="$i18n('join_region.error.is_state')"
      />
      <span
        v-if="selectedRegionType === 6"
        v-html="$i18n('join_region.error.is_country')"
      />
      <span
        v-if="selectedRegionType === 8"
        v-html="$i18n('join_region.error.is_big_city')"
      />
    </div>
  </b-modal>
</template>

<script>
// Stores
import DataRegions from '@/stores/regions'
import DataUser from '@/stores/user'
// Others
import { pulseError, showLoader, hideLoader } from '@/script'
export default {
  name: 'JoinRegionModal',
  data () {
    return {
      selected: [0],
      regions: [],
      base: [],
    }
  },
  /*
    1: Stadt
    2: Bezirk
    3: Region
    5: Bundesland
    6: Land
    7: Arbeitsgruppe
    8: Großstadt
    9: Stadtteil
  */
  computed: {
    isLoggedIn () {
      return DataUser.getters.isLoggedIn()
    },
    regionIsInValid () {
      return ![1, 9, 2, 3].includes(this.selectedRegionType)
    },
    selectedRegionList () {
      return this.selected
    },
    selectedRegionType () {
      return this.selectedRegion?.type
    },
    selectedRegion () {
      const regions = [...this.base]
      this.regions.map(region => region.list).forEach(r => regions.push(...r))
      const last = this.selected[this.selected.length - 1]
      return regions.find(region => region.id === last)
    },
    regionsList () {
      return this.regions
        .filter(region => this.selectedRegionList.includes(region.id) && region.list.length > 0)
    },
  },
  watch: {
    selected: {
      async handler (ids) {
        for (const [index, id] of ids.entries()) {
          const region = this.regions.find(r => r.id === id)
          if (id && !region) {
            let list = await DataRegions.mutations.fetchChoosedRegionChildren(id)
            list = list.filter(r => r.type !== 7) // removes all arbeitsgruppen
            if (list.length > 0) {
              this.regions.push({ id, list })
            }
          } else if (id === null) {
            this.selected.length = index
          }
        }
      },
      deep: true,
    },
  },
  methods: {
    async joinRegion () {
      try {
        showLoader()
        await DataRegions.mutations.joinRegion(this.selectedRegion.id)
      } catch (err) {
        console.log(err)
        pulseError('In diesen Bezirk kannst Du Dich nicht eintragen.')
      } finally {
        hideLoader()
      }
    },
    async showModal () {
      this.selected = [0]
      this.base = await DataRegions.mutations.fetchChoosedRegionChildren(0)
    },
    async resetModal () {
      this.selected = [0]
    },
  },
}
</script>

<style lang="scss" scoped>
.selector select {
    margin-bottom: 0.25rem;
}
</style>
