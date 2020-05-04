<template>
  <div class="store-edit container bootstrap mb-3 pb-3">
    <b-card no-body>
      <b-tabs
        content-class="mt-2"
        card
        pills
      >
        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.text.header')" no-body>
          <b-row class="store-text">
            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.text.title')" />
              <b-card-text class="store-title">
                <b-form-input
                  v-model.trim="form.title"
                  :placeholder="$i18n('storeedit.text.titlePlaceholder')"
                  @change="change($event, 'title')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.text.publicInfo')" />
              <b-card-text>
                <b-alert show>
                  <i class="fas fa-info-circle" />
                  <div class="info-text">
                    <span>
                      {{ $i18n('storeedit.text.publicInfoLabel') }}
                    </span>
                    <span class="font-weight-bolder">
                      {{ $i18n('storeedit.text.publicInfoWarning') }}
                    </span>
                    <span>
                      {{ $i18n('storeedit.text.publicInfoDetails') }}
                    </span>
                  </div>
                </b-alert>

                <b-form-textarea
                  v-model.trim="form.publicInfo"
                  :state="(form.publicInfo && form.publicInfo.length > 180) ? false : null"
                  :placeholder="$i18n('storeedit.text.publicInfo') + '...'"
                  rows="4"
                  max-rows="6"
                  @change="change($event, 'publicInfo')"
                />
              </b-card-text>
            </b-col>

            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.text.particularities')" />
              <b-card-text>
                <b-form-textarea
                  v-model.trim="form.particularities"
                  :placeholder="$i18n('storeedit.text.particularities') + '...'"
                  rows="13"
                  @change="change($event, 'particularities')"
                />
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.fetch.header')" no-body>
          <b-row class="store-fetch">
            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.fetch.weight')" />
              <b-card-text>
                <b-form-select
                  v-model="form.weight"
                  :options="weightOptions"
                  @change="change($event, 'weight')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.fetch.time')" />
              <b-card-text class="store-time">
                <b-form-select
                  v-model="form.time"
                  :options="timeOptions"
                  @change="change($event, 'time')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.fetch.prefetchtime')" />
              <b-card-text>
                <b-form-select
                  v-model="form.prefetchtime"
                  :options="prefetchtimeOptions"
                  @change="change($event, 'prefetchtime')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.fetch.teamStatus')" />
              <b-card-text>
                <b-form-select
                  v-model="form.teamStatus"
                  :options="teamStatusOptions"
                  @change="change($event, 'teamStatus')"
                />
              </b-card-text>
            </b-col>

            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.fetch.foodTypes')" />
              <b-card-text>
                <b-form-group>
                  <b-form-checkbox-group
                    v-model="form.foodType"
                    :options="foodTypeOptions"
                    value-field="id"
                    text-field="name"
                    @change="change($event, 'foodType')"
                  />
                </b-form-group>
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.coop.header')" no-body>
          <b-row class="store-coop">
            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.coop.start')" />
              <b-card-text>
                <b-form-datepicker
                  v-model="form.start"
                  type="text"
                  @input="change($event, 'start')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.difficulty')" />
              <b-card-text>
                <b-form-select
                  v-model="form.difficulty"
                  :options="difficultyOptions"
                  @change="change($event, 'difficulty')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.publicity')" />
              <b-card-text>
                <b-form-select
                  v-model="form.publicity"
                  :options="yesNoOptions"
                  @change="change($event, 'publicity')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.sticker')" />
              <b-card-text>
                <b-form-select
                  v-model="form.sticker"
                  :options="yesNoOptions"
                  @change="change($event, 'sticker')"
                />
              </b-card-text>
            </b-col>

            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.coop.contactPerson')" />
              <b-card-text>
                <b-form-input
                  v-model="form.contactPerson"
                  @change="change($event, 'contactPerson')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.contactPhone')" />
              <b-card-text>
                <b-form-input
                  v-model.trim="form.contactPhone"
                  @change="change($event, 'contactPhone')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.contactFax')" />
              <b-card-text>
                <b-form-input
                  v-model.trim="form.contactFax"
                  @change="change($event, 'contactFax')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.contactMail')" />
              <b-card-text>
                <b-form-input
                  v-model.trim="form.contactMail"
                  @change="change($event, 'contactMail')"
                />
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.store.header')" no-body>
          <b-row class="store-status">
            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.store.status')" />
              <b-card-text>
                <b-alert show>
                  <i class="fas fa-info-circle" />
                  <div class="info-text">
                    <!-- eslint-disable vue/html-closing-bracket-newline -->
                    <span
                      v-html="$i18n('storeedit.store.statusInfo', {
                        statusDetails: $i18n('storeedit.store.statusDetails'),
                        contactDetails: $i18n('storeedit.store.contactDetails')
                      })" />
                    <span>
                      {{ $i18n('storeedit.store.statusReason') }}
                    </span>
                  </div>
                </b-alert>
                <b-form-select
                  v-model="form.status"
                  :options="storeStatusOptions"
                  @change="change($event, 'status')"
                />
              </b-card-text>
            </b-col>

            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.store.chain')" />
              <b-card-text>
                <b-form-select
                  v-model="form.chain"
                  :options="chainOptions"
                  value-field="id"
                  text-field="name"
                  @change="change($event, 'chain')"
                >
                  <template v-slot:first>
                    <b-form-select-option
                      :value="null"
                      disabled
                    >
                      {{ $i18n('storeedit.optionalDefault') }}
                    </b-form-select-option>
                  </template>
                </b-form-select>
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.store.category')" />
              <b-card-text>
                <b-form-select
                  v-model="form.category"
                  :options="categoryOptions"
                  value-field="id"
                  text-field="name"
                  @change="change($event, 'category')"
                >
                  <template v-slot:first>
                    <b-form-select-option
                      :value="null"
                      disabled
                    >
                      {{ $i18n('storeedit.optionalDefault') }}
                    </b-form-select-option>
                  </template>
                </b-form-select>
              </b-card-text>

              <b-card-text>
                <b-row align-h="end">
                  <!-- should be sm="6" with two buttons -->
                  <b-col
                    v-for="btn in addDatabaseEntryButtons"
                    :key="btn.id"
                    sm="12"
                    class="mb-2"
                  >
                    <b-button
                      v-b-toggle="btn.id"
                      variant="outline-secondary"
                      :class="btn.id"
                      block
                    >
                      <i class="fas fa-plus-circle" />
                      {{ btn.text }}
                    </b-button>
                  </b-col>
                </b-row>
                <b-collapse id="newchain">
                  <b-input-group>
                    <b-form-input
                      v-model.trim="newchainText"
                      class="form-control with-border"
                    />
                    <b-input-group-append>
                      <b-button
                        :disabled="!newchainText.trim()"
                        variant="secondary"
                        type="submit"
                        size="sm"
                        @click.prevent="addDatabaseEntry('newchain')"
                      >
                        <i class="fas fa-plus" />
                      </b-button>
                    </b-input-group-append>
                  </b-input-group>
                </b-collapse>
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.location.header')" no-body>
          <b-row class="store-location">
            <b-col>
              <b-card-title :title="$i18n('storeedit.location.seeBelow')" />
            </b-col>
          </b-row>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>

<script>
import _ from 'underscore'
import i18n from '@/i18n'
import { addStoreChain, updateStore } from '@/api/stores'

const BAD_TO_GOOD = {
  betrieb_kategorie_id: 'category',
  kette_id: 'chain',
  ansprechpartner: 'contactPerson',
  telefon: 'contactPhone',
  fax: 'contactFax',
  email: 'contactMail',
  ueberzeugungsarbeit: 'difficulty',
  lebensmittel: 'foodType',
  besonderheiten: 'particularities',
  prefetchtime: 'prefetchtime',
  presse: 'publicity',
  public_info: 'publicInfo',
  begin: 'start',
  betrieb_status_id: 'status',
  sticker: 'sticker',
  team_status: 'teamStatus',
  public_time: 'time',
  name: 'title',
  abholmenge: 'weight'
}
const GOOD_TO_BAD = _.invert(BAD_TO_GOOD)

export default {
  props: {
    storeData: { type: Object, required: true },
    mayManageStoreChains: { type: Boolean, default: false },
    foodTypeOptions: { type: Array, default: null },
    categoryOptions: { type: Array, default: null },
    chainOptions: { type: Array, default: null }
  },
  data: function () {
    return {
      form: _.mapObject(GOOD_TO_BAD, (val, key) => { return this.storeData[val] }),
      newchainText: ''
    }
  },
  computed: {
    addDatabaseEntryButtons () {
      return _.filter([{
        text: i18n('storeedit.store.newChain'),
        id: 'newchain',
        visible: this.mayManageStoreChains
      }, {
        text: i18n('storeedit.store.newCategory'),
        id: 'newcategory',
        visible: false // TODO Re-implement this after cleaning up categories in DB
      }], (btn) => { return btn.visible })
    },
    difficultyOptions () {
      return [
        { value: 0, text: i18n('storeedit.dropdownDefault'), disabled: true },
        { value: 1, text: i18n('storeedit.coop.difficulty1') },
        { value: 2, text: i18n('storeedit.coop.difficulty2') },
        { value: 3, text: i18n('storeedit.coop.difficulty3') },
        { value: 4, text: i18n('storeedit.coop.difficulty4') }
      ]
    },
    prefetchtimeOptions () {
      return [
        { value: null, text: i18n('storeedit.dropdownDefault'), disabled: true },
        { value: 604800, text: i18n('storeedit.fetch.week') },
        { value: 1209600, text: i18n('storeedit.fetch.weeks', { count: 2 }) },
        { value: 1814400, text: i18n('storeedit.fetch.weeks', { count: 3 }) },
        { value: 2419200, text: i18n('storeedit.fetch.weeks', { count: 4 }) }
      ]
    },
    storeStatusOptions () {
      return [
        { value: null, text: i18n('storeedit.dropdownDefault'), disabled: true },
        { value: 0, text: i18n('storestatus.0') },
        { value: 1, text: i18n('storestatus.1') },
        { value: 2, text: i18n('storestatus.2') },
        { value: 3, text: i18n('storestatus.3') },
        { value: 4, text: i18n('storestatus.4') },
        { value: 5, text: i18n('storestatus.5') },
        { value: 6, text: i18n('storestatus.6') }
      ]
    },
    teamStatusOptions () {
      return [
        { value: null, text: i18n('storeedit.dropdownDefault'), disabled: true },
        { value: 0, text: i18n('storeedit.fetch.teamStatus0') },
        { value: 1, text: i18n('storeedit.fetch.teamStatus1') },
        { value: 2, text: i18n('storeedit.fetch.teamStatus2') }
      ]
    },
    timeOptions () {
      return [
        { value: null, text: i18n('storeedit.dropdownDefault'), disabled: true },
        { value: 0, text: i18n('storeedit.fetch.time0') },
        { value: 1, text: i18n('storeedit.fetch.time1') },
        { value: 2, text: i18n('storeedit.fetch.time2') },
        { value: 3, text: i18n('storeedit.fetch.time3') },
        { value: 4, text: i18n('storeedit.fetch.time4') }
      ]
    },
    weightOptions () {
      return [
        { value: 0, text: i18n('storeedit.dropdownDefault'), disabled: true },
        { value: 1, text: i18n('storeedit.fetch.weight1') },
        { value: 2, text: i18n('storeedit.fetch.weight2') },
        { value: 3, text: i18n('storeedit.fetch.weight3') },
        { value: 4, text: i18n('storeedit.fetch.weight4') },
        { value: 5, text: i18n('storeedit.fetch.weight5') },
        { value: 6, text: i18n('storeedit.fetch.weight6') },
        { value: 7, text: i18n('storeedit.fetch.weight7') }
      ]
    },
    yesNoOptions () {
      return [
        { value: null, text: i18n('storeedit.unspecified') },
        { value: '1', text: i18n('yes') },
        { value: '0', text: i18n('no') }
      ]
    }
  },
  methods: {
    async change (newValue, field) {
      const storeId = this.storeData.id
      const dbField = GOOD_TO_BAD[field]
      if (dbField) {
        await updateStore(storeId, dbField, newValue)
      } else {
        console.warn('Tried updating unknown store field:', field)
      }
    },
    async addDatabaseEntry (field) {
      const newValue = this[field + 'Text']
      this[field + 'Text'] = ''
      if (field === 'newchain') {
        await addStoreChain(newValue)
      } else {
        console.warn('Tried adding untethered database entry:', { newValue, field })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.store-edit {
  .card {
    padding: 10px;
  }
  .card-title {
    font-size: 16px;
    padding-left: 8px;
    border-left: 8px solid var(--fs-beige);

    padding: 5px 10px;
    background-color: var(--fs-white);
    color: var(--fs-brown);
  }
  /deep/ .custom-control-label {
    line-height: 1.5;
    font-size: 20px;
    min-width: 250px;
  }
  /deep/ .alert {
    display: flex;
    border: 0;
    margin: 15px 0;
    background-color: var(--fs-white);
    border: 2px solid var(--fs-beige);
    color: var(--fs-brown);
    flex-direction: row;

    a {
      color: var(--fs-green);
      font-weight: bolder;
    }

    i.fas {
      margin-right: 20px;
      align-self: center;
      font-size: 24px;
    }

    .info-text span {
      display: inline-block;

      &:not(:first-of-type) {
        padding-top: 5px;
      }
    }
  }
}
</style>
