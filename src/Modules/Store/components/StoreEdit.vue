<template>
  <div class="store-edit container bootstrap mb-3 pb-3">
    <b-card no-body>
      <b-tabs
        content-class="mt-2"
        card
        pills
      >
        <!-- STORE TEXT -->
        <b-tab
          :title="$i18n('storeedit.text.header')"
          active
          no-body
        >
          <b-row class="store-text">
            <b-col sm="6">
              <!-- store title -->
              <b-card-title class="required">
                {{ $i18n('storeedit.text.title') }}
              </b-card-title>
              <b-card-text>
                <b-form-input
                  v-model.trim="form.title"
                  :placeholder="$i18n('storeedit.text.titlePlaceholder')"
                  @change="titleChange"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.text.publicInfo') }}
              </b-card-title>
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
                  :state="form.publicInfo.length > 180 ? false : null"
                  rows="4"
                  max-rows="6"
                  @change="$emit('update:form', form)"
                />
              </b-card-text>
              <!-- -->
            </b-col>

            <b-col sm="6">
              <!-- store particularities -->
              <b-card-title>
                {{ $i18n('storeedit.text.particularities') }}
              </b-card-title>
              <b-card-text>
                <b-form-textarea
                  v-model.trim="form.particularities"
                  rows="13"
                  @change="$emit('update:form', form)"
                />
              </b-card-text>
              <!-- -->
            </b-col>
          </b-row>
        </b-tab>

        <b-tab
          :title="$i18n('storeedit.fetch.header')"
          no-body
        >
          <b-row class="store-fetch">
            <b-col sm="6">
              <!-- fetch amount -->
              <b-card-title>
                {{ $i18n('storeedit.fetch.amount') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.weight"
                  :options="weightOptions"
                  value-field="id"
                  text-field="name"
                  @change="weightChange"
                >
                  <template v-slot:first>
                    <b-form-select-option
                      :value="null"
                      disabled
                    >
                      {{ $i18n('storeedit.dropdownDefault') }}
                    </b-form-select-option>
                  </template>
                </b-form-select>
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.fetch.time') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.time"
                  :options="timeOptions"
                  @change="timeChange"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.fetch.prefetchtime') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.prefetchtime"
                  :options="prefetchtimeOptions"
                  @change="prefetchtimeChange"
                />
              </b-card-text>
              <!-- -->
            </b-col>

            <b-col sm="6">
              <!-- food types -->
              <b-card-title>
                {{ $i18n('storeedit.fetch.foodTypes') }}
              </b-card-title>
              <b-card-text>
                <!-- b-form-group :label="$i18n('storeedit.fetch.foodTypes')" -->
                <b-form-group>
                  <b-form-checkbox-group
                    v-model="form.foodType"
                    :options="foodTypeOptions"
                    value-field="id"
                    text-field="name"
                  />
                </b-form-group>
              </b-card-text>
              <!-- -->
            </b-col>
          </b-row>
        </b-tab>

        <b-tab
          :title="$i18n('storeedit.coop.header')"
          no-body
        >
          <b-row class="store-coop">
            <b-col sm="6">
              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.coop.start') }}
              </b-card-title>
              <b-card-text>
                <b-form-datepicker
                  v-model="form.start"
                  type="text"
                  @change="startChange"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.coop.difficulty') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.difficulty"
                  :options="difficultyOptions"
                  @change="difficultyChange"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.coop.publicity') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.publicity"
                  :options="yesNoOptions"
                  @change="publicityChange"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.coop.sticker') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.sticker"
                  :options="yesNoOptions"
                  @change="stickerChange"
                />
              </b-card-text>
            </b-col>

            <b-col sm="6">
              <b-card-title>
                {{ $i18n('storeedit.coop.contactPerson') }}
              </b-card-title>
              <b-card-text>
                <b-form-input
                  v-model="form.contactPerson"
                  @change="$emit('update:form', form)"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.coop.contactPhone') }}
              </b-card-title>
              <b-card-text>
                <b-form-input
                  v-model.trim="form.contactPhone"
                  @change="$emit('update:form', form)"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.coop.contactFax') }}
              </b-card-title>
              <b-card-text>
                <b-form-input
                  v-model.trim="form.contactFax"
                  @change="$emit('update:form', form)"
                />
              </b-card-text>
              <!-- -->

              <!-- -->
              <b-card-title>
                {{ $i18n('storeedit.coop.contactMail') }}
              </b-card-title>
              <b-card-text>
                <b-form-input
                  v-model.trim="form.contactMail"
                  @change="$emit('update:form', form)"
                />
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <b-tab
          :title="$i18n('storeedit.store.header')"
          no-body
        >
          <b-row class="store-status">
            <b-col sm="6">
              <!-- store status -->
              <b-card-title>
                {{ $i18n('storeedit.store.status') }}
              </b-card-title>
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
                  :options="statusOptions"
                  value-field="id"
                  text-field="name"
                  @change="statusChange"
                >
                  <template v-slot:first>
                    <b-form-select-option
                      :value="null"
                      disabled
                    >
                      {{ $i18n('storeedit.dropdownDefault') }}
                    </b-form-select-option>
                  </template>
                </b-form-select>
              </b-card-text>
              <!-- -->
            </b-col>

            <b-col sm="6">
              <b-card-title>
                {{ $i18n('storeedit.store.chain') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.chain"
                  :options="chainOptions"
                  value-field="id"
                  text-field="name"
                  @change="chainChange"
                >
                  <template v-slot:first>
                    <b-form-select-option
                      :value="null"
                      disabled
                    >
                      {{ $i18n('storeedit.dropdownDefault') }}
                    </b-form-select-option>
                  </template>
                </b-form-select>
              </b-card-text>

              <b-card-title>
                {{ $i18n('storeedit.store.category') }}
              </b-card-title>
              <b-card-text>
                <b-form-select
                  v-model="form.category"
                  :options="categoryOptions"
                  value-field="id"
                  text-field="name"
                  @change="categoryChange"
                >
                  <template v-slot:first>
                    <b-form-select-option
                      :value="null"
                      disabled
                    >
                      {{ $i18n('storeedit.dropdownDefault') }}
                    </b-form-select-option>
                  </template>
                </b-form-select>
              </b-card-text>

              <b-card-text class="d-flex justify-content-between">
                <button class="btn btm-sm btn-secondary">
                  <i class="fas fa-plus-circle" />
                  {{ $i18n('storeedit.store.newChain') }}
                </button>
                <button class="btn btm-sm btn-secondary">
                  <i class="fas fa-plus-circle" />
                  {{ $i18n('storeedit.store.newCategory') }}
                </button>
              </b-card-text>
            </b-col>
          </b-row>
        </b-tab>

        <b-tab
          class="hidden"
          :title="$i18n('storeedit.location.header')"
          no-body
        >
          <b-row class="store-location">
            <b-col>
              <!--
              <div v-html="regionPickerHtml" />
              -->
              TODO (see below)
            </b-col>
          </b-row>
        </b-tab>
      </b-tabs>
    </b-card>
    <!-- MISSING: -->
    <!-- INTEGRATE REGION PICKER -->
    <!-- INTEGRATE ADDRESS PICKER -->
    <!-- ADDRESS FIELDS (x4) -->
    <!-- CREATE NEW CHAIN FUNCTIONALITY -->
    <!-- CREATE NEW CATEGORY FUNCTIONALITY -->
  </div>
</template>

<script>
import _ from 'underscore'
import i18n from '@/i18n'
import { updateStoreTitle } from '@/api/stores'

export default {
  props: {
    // regionPickerHtml: { type: String, required: true },
    storeData: { type: Object, default: null },
    weightOptions: { type: Array, default: null },
    foodTypeOptions: { type: Array, default: null },
    statusOptions: { type: Array, default: null },
    categoryOptions: { type: Array, default: null },
    chainOptions: { type: Array, default: null }
  },
  data: function () {
    console.log('DATA', this.storeData)
    return {
      form: {
        category: this.storeData.betrieb_kategorie_id,
        chain: this.storeData.kette_id,
        contactPerson: this.storeData.ansprechpartner,
        contactPhone: this.storeData.telefon,
        contactFax: this.storeData.fax,
        contactMail: this.storeData.email,
        difficulty: this.storeData.ueberzeugungsarbeit,
        foodType: this.storeData.lebensmittel,
        particularities: this.storeData.besonderheiten,
        prefetchtime: this.storeData.prefetchtime,
        publicity: this.storeData.presse,
        publicInfo: this.storeData.public_info,
        start: this.storeData.begin,
        status: this.storeData.betrieb_status_id,
        sticker: this.storeData.sticker,
        time: this.storeData.public_time,
        title: this.storeData.name,
        weight: this.storeData.abholmenge
      }
    }
  },
  computed: {
    difficultyOptions () {
      return [
        { value: null, text: i18n('storeedit.dropdownDefault'), disabled: true },
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
    yesNoOptions () {
      return [
        { value: null, text: i18n('storeedit.unspecified') },
        { value: '1', text: i18n('yes') },
        { value: '0', text: i18n('no') }
      ]
    },
    fooOptions () {
      return [{ value: null, text: 'Please select', disabled: true }].concat(_.flatten([]))
    }
  },
  methods: {
    categoryChange () {
      this.$emit('update:form', this.form)
    },
    chainChange () {
      this.$emit('update:form', this.form)
    },
    difficultyChange () {
      this.$emit('update:form', this.form)
    },
    foodTypeChange () {
      this.$emit('update:form', this.form)
    },
    prefetchtimeChange () {
      this.$emit('update:form', this.form)
    },
    publicityChange () {
      this.$emit('update:form', this.form)
    },
    startChange () {
      this.$emit('update:form', this.form)
    },
    statusChange () {
      this.$emit('update:form', this.form)
    },
    stickerChange () {
      this.$emit('update:form', this.form)
    },
    timeChange () {
      this.$emit('update:form', this.form)
    },
    async titleChange () {
      this.$emit('update:form', this.form)
      await updateStoreTitle(this.storeData.id, this.form.title)
    },
    weightChange () {
      this.$emit('update:form', this.form)
    },
    submit () {
      console.log(this.form)
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

    &.required {
      background-color: var(--fs-beige);
      border-left-color: var(--fs-brown);

      &::after {
        content: '*';
        float: right;
        text-align: right;
        font-size: 20px;
      }
    }
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
