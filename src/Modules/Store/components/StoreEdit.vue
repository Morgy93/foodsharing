<template>
  <div class="store-edit container bootstrap mb-3 pb-3">
    <b-card no-body>
      <b-tabs
        content-class="mt-2"
        justified
        pills
        card
        @activate-tab="switchTab"
      >
        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.text.header')" no-body>
          <b-row class="store-section store-text">
            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.text.title')" />
              <b-card-text class="store-title">
                <b-input
                  v-model="form.title"
                  :placeholder="$i18n('storeedit.text.titlePlaceholder')"
                  lazy
                  trim
                  @change="change('title')"
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
                  v-model="form.publicInfo"
                  :state="(form.publicInfo && form.publicInfo.length > 180) ? false : null"
                  :placeholder="$i18n('storeedit.text.publicInfo') + '…'"
                  rows="6"
                  lazy
                  trim
                  @change="change('publicInfo')"
                />
              </b-card-text>
            </b-col>

            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.text.particularities')" />
              <b-card-text>
                <b-alert show>
                  <i class="fas fa-info-circle" />
                  <div class="info-text">
                    <span>
                      {{ $i18n('storeedit.text.particularitiesInfo') }}
                    </span>
                    <span v-html="$i18n('info.md')" />
                  </div>
                </b-alert>

                <b-form-textarea
                  v-model="form.particularities"
                  :placeholder="$i18n('storeedit.text.particularities') + '…'"
                  rows="12"
                  lazy
                  trim
                  @change="change('particularities')"
                />
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.fetch.header')" no-body>
          <b-row class="store-section store-fetch">
            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.fetch.weight')" />
              <b-card-text>
                <b-form-select
                  v-model="form.weight"
                  :options="weightOptions"
                  @change="change('weight')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.fetch.time')" />
              <b-card-text class="store-time">
                <b-form-select
                  v-model="form.time"
                  :options="timeOptions"
                  @change="change('time')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.fetch.prefetchtime')" />
              <b-card-text>
                <b-form-select
                  v-model="form.prefetchtime"
                  :options="prefetchtimeOptions"
                  @change="change('prefetchtime')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.fetch.teamStatus')" />
              <b-card-text>
                <b-form-select
                  v-model="form.teamStatus"
                  :options="teamStatusOptions"
                  @change="change('teamStatus')"
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
                    @change="change('foodType')"
                  />
                </b-form-group>
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.coop.header')" no-body>
          <b-row class="store-section store-coop">
            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.coop.start')" />
              <b-card-text>
                <b-form-datepicker
                  v-model="form.start"
                  type="text"
                  @input="change('start')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.difficulty')" />
              <b-card-text>
                <b-form-select
                  v-model="form.difficulty"
                  :options="difficultyOptions"
                  @change="change('difficulty')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.publicity')" />
              <b-card-text>
                <b-form-select
                  v-model="form.publicity"
                  :options="yesNoOptions"
                  @change="change('publicity')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.sticker')" />
              <b-card-text>
                <b-form-select
                  v-model="form.sticker"
                  :options="yesNoOptions"
                  @change="change('sticker')"
                />
              </b-card-text>
            </b-col>

            <b-col sm="6">
              <b-card-title :title="$i18n('storeedit.coop.contactPerson')" />
              <b-card-text>
                <b-form-input
                  v-model="form.contactPerson"
                  @change="change('contactPerson')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.contactPhone')" />
              <b-card-text>
                <b-form-input
                  v-model="form.contactPhone"
                  lazy
                  trim
                  @change="change('contactPhone')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.contactFax')" />
              <b-card-text>
                <b-form-input
                  v-model="form.contactFax"
                  lazy
                  trim
                  @change="change('contactFax')"
                />
              </b-card-text>

              <b-card-title :title="$i18n('storeedit.coop.contactMail')" />
              <b-card-text>
                <b-form-input
                  v-model="form.contactMail"
                  lazy
                  trim
                  @change="change('contactMail')"
                />
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <!-- eslint-disable-next-line vue/max-attributes-per-line -->
        <b-tab :title="$i18n('storeedit.store.header')" no-body>
          <b-row class="store-section store-status">
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
                  @change="change('status')"
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
                  @change="change('chain')"
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
                  @change="change('category')"
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
                      v-model="newchainText"
                      class="form-control with-border"
                      lazy
                      trim
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
          <b-row class="store-section store-location">
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
import $ from 'jquery'
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

const rawToast = {
  toaster: 'b-toaster-top-right',
  noCloseButton: true,
  autoHideDelay: 3000, // milliseconds
  solid: false, // transparent
  isStatus: true // accessibility
}

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
        { value: 6, text: i18n('storestatus.6') },
        { value: 7, text: i18n('storestatus.7') }
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
    switchTab (newIndex, oldIndex) {
      const locationTabIndex = 4
      if (newIndex === locationTabIndex) {
        // show legacy fields/form when opening Location tab
        $('.store-legacydata').removeClass('d-none')
      } else if (oldIndex === locationTabIndex) {
        // hide legacy fields/form since we are leaving that tab
        $('.store-legacydata').addClass('d-none')
      }
    },
    async change (field) {
      const storeId = this.storeData.id
      const newValue = this.form[field]
      const dbField = GOOD_TO_BAD[field]
      if (dbField) {
        try {
          await updateStore(storeId, dbField, newValue)
          this.successToast()
        } catch (err) {
          console.error(`Could not update field ${field} to '${newValue}'.`, err.message)
          this.burnedToast()
          // TODO reset vue data to old, non-updated value
        }
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
    },
    successToast () {
      this.$bvToast.toast(' ', Object.assign({
        title: i18n('saved'),
        variant: 'success'
      }, rawToast))
    },
    burnedToast () {
      this.$bvToast.toast(' ', Object.assign({
        title: i18n('no'),
        variant: 'danger'
      }, rawToast))
    }
  }
}
</script>

<style lang="scss" scoped>
.store-edit {
  .card {
    padding: 10px;
  }
  ::v-deep .tabs .card-header:first-child {
    border-radius: 6px;
  }
  .card-title {
    font-size: 16px;
    padding-left: 8px;
    border-left: 8px solid var(--fs-beige);

    padding: 5px 10px;
    background-color: var(--fs-white);
    color: var(--fs-brown);
  }

  // column separation, same combined value as the bootstrap margin-bottom for `p`
  .row.store-section > div {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
  }

  ::v-deep .custom-control-label {
    line-height: 1.5;
    font-size: 20px;
    min-width: 250px;
  }
  ::v-deep .alert {
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

<style lang="scss">
.old-storeedit {
  padding-bottom: 15px;
}

.b-toast.b-toast-danger,
.b-toast.b-toast-success {
  font-size: 24px;
  float: right;
  width: -webkit-fill-available;
  width: fit-content;

  .toast-header > strong::before {
    font-family: "Font Awesome 5 Free", monospace;
    font-weight: 900;
    font-style: normal;
    font-size: inherit;
    text-rendering: auto;
    display: inline-block;
    padding: 5px 10px;
  }
}

.b-toast.b-toast-danger {
  .toast-header > strong::before {
    content: '\f071'; // fa-exclamation-triangle
  }
}
.b-toast.b-toast-success {
  .toast-header > strong::before {
    content: '\f00c'; // fa-check
  }
}
</style>
