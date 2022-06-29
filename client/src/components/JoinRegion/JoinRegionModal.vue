<template>
  <div
    :id="$options.name"
    tabindex="-1"
    class="testing-region-join modal fade"
    :aria-labelledby="$options.name"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md">
      <div
        v-if="isVisible"
        class="modal-content"
      >
        <div class="modal-header">
          <h3 v-html="$i18n('join_region.headline')" />
        </div>
        <div class="modal-body">
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
                v-html="$i18n('globals.select')"
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
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="testing-region-join-close btn btn-light"
            data-dismiss="modal"
            @click="close"
            v-html="$i18n('globals.close')"
          />
          <button
            type="button"
            :disabled="regionIsInValid"
            class="testing-region-join-submit btn btn-secondary"
            data-dismiss="modal"
            @click="joinRegion"
            v-html="$i18n('globals.save')"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script>
// Stores
import DataRegions from '@/stores/regions'
import DataUser from '@/stores/user'
// Others
import { pulseError, showLoader, hideLoader } from '@/script'
// Mixins
import ModalHiderMixin from '@/mixins/ModalHiderMixin'
export default {
  name: 'JoinRegionModal',
  mixins: [ModalHiderMixin],
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
    isVisible: {
      async handler (val) {
        if (val && this.isLoggedIn) {
          this.base = await DataRegions.mutations.fetchChoosedRegionChildren(0)
        }
      },
      deep: true,
    },
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
    async close () {
      this.selected = [0]
    },
  },
}
</script>

<style lang="scss" scoped>
.modal-footer {
  display: flex;
  justify-content: space-between;
}

.selector select {
    margin-bottom: 0.25rem;
}
</style>
