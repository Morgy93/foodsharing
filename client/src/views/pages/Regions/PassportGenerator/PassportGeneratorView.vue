<template>
  <!-- eslint-disable -->
  <section class="bootstrap container-fluid">
    <div class="row">
      <div class="col-9">
        <Container :title="this.main_container_title">
          <div class="card-body bg-white">
            <b-table :items="foodsaver" :fields="table.fields" :responsive="true" selectable select-mode="multi">
              <template #cell(name)="data">
                <a :href="getProfilUrl(data.item.id)">{{ data.item.name }}</a> <i>({{ data.item.role_name }})</i>
              </template>


              <template #cell(photo)="data">
                <Avatar
                  :is-sleeping="data.item.sleepStatus"
                  :url="data.item.photo"
                />
              </template>

              <template #cell(is_verified)="data">
                <i v-if="data.item.is_verified" class="fas fa-check-circle fa-2x text-success"
                   v-b-modal.deverification-modal
                   @click="foodsaverIdForDeverification = data.item.id"
                />
                <i v-if="!data.item.is_verified" class="fas fa-times-circle fa-2x text-danger"
                   v-b-modal.verification-modal
                   @click="foodsaverIdForVerification = data.item.id"
                />
              </template>

              <template #cell(remove)="data">
                <button class="btn btn-block btn-outline-danger" v-b-modal.remove-from-region-modal
                        @click="foodsaverIdForRemoving = data.item.id"
                >
                  <i class="fas fa-sign-out-alt"></i>
                </button>
              </template>
            </b-table>

            <hr class="my-5 bg-success">

            <table class="table">
              <thead>
                <tr>
                  <td></td>
                  <td>{{ $i18n('pass.photo') }}</td>
                  <td>{{ $i18n('pass.name') }}</td>
                  <td>{{ $i18n('pass.date') }}</td>
                  <td>Letzter Login</td>
                  <td>{{ $i18n('pass.verified') }}</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in foodsaver"
                  :key="user.id"
                >
                  <td>
                    <input type="checkbox" :checked="user.checked"/>
                  </td>
                  <td>
                    <Avatar
                      :is-sleeping="user.sleepStatus"
                      :url="user.photo"
                    />
                  </td>
                  <td>
                    <a :href="getProfilUrl(user.id)">{{ user.name }}</a> ({{ user.role_name }})
                  </td>
                  <td>{{ user.displayed_data.last_pass_date }}</td>
                  <td>{{ user.displayed_data.last_login_date }}</td>
                  <td>
                    <i v-if="user.is_verified" class="fas fa-check-circle fa-2x text-success"
                       v-b-modal.deverification-modal
                       @click="foodsaverIdForDeverification = user.id"
                    />
                    <i v-if="!user.is_verified" class="fas fa-times-circle fa-2x text-danger"
                       v-b-modal.verification-modal
                       @click="foodsaverIdForVerification = user.id"
                    />
                  </td>
                  <td>
                    <button class="btn btn-block btn-outline-danger" v-b-modal.remove-from-region-modal
                            @click="foodsaverIdForRemoving = user.id"
                    >
                      <i class="fas fa-sign-out-alt"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </Container>
      </div>

      <div class="col-3">
        <Container :title="options_container_title">
          <div class="card-body bg-white">
            <button class="btn btn-sm btn-block btn-primary" @click="() => {this.foodsaver.forEach(foodsaver => foodsaver.checked = true)}">
              {{ $i18n('pass.nav.select') }}
            </button>
            <button class="btn btn-sm btn-block btn-primary" @click="() => {this.foodsaver.forEach(foodsaver => foodsaver.checked = false)}">
              {{ $i18n('pass.nav.deselect') }}
            </button>
            <button class="btn btn-sm btn-block btn-success">
              {{ $i18n('pass.nav.generate') }}
            </button>
          </div>
        </Container>
      </div>
    </div>

    <b-modal id="verification-modal" title="Verification" ok-title="Yes" @ok="() => verificate(this.foodsaverIdForVerification)" >
      <p>{{ $i18n('pass.verify.confirm') }} {{ $i18n('pass.verify.text') }}</p>
    </b-modal>

    <b-modal id="deverification-modal" title="Deverification" ok-title="Yes" @ok="() => deverificate(this.foodsaverIdForDeverification)" >
      <p>Do you want remove the verification status?</p>
    </b-modal>

    <b-modal id="remove-from-region-modal" title="Remove from region" ok-title="Yes" @ok="() => removeFromRegion(this.foodsaverIdForRemoving)" >
      <p>Do you want remove the foodsaver?</p>
    </b-modal>
  </section>
</template>

<script>

import Container from '@/components/Container/Container.vue'
import Avatar from '@/components/Avatar.vue'
import { listRegionMembersDetailed, removeMember } from '@/api/regions'
import { pulseError } from '@/script'
import i18n from '@/i18n'
import { deverifyUser, verifyUser } from '@/api/verification'

export default {
  name: 'PassportGeneratorView',
  components: {
    Container,
    Avatar,
  },
  props: {
    regionId: {
      type: Number,
      required: true,
    },
  },
  data () {
    return {
      main_container_title: 'Foodsaver',
      options_container_title: i18n('pass.nav.options'),
      foodsaver: [],
      foodsaverIdForDeverification: null,
      foodsaverIdForVerification: null,
      foodsaverIdForRemoving: null,
      table: {
        fields: [
          {
            key: 'photo',
            label: i18n('pass.photo'),
            sortable: false,
          },
          {
            key: 'name',
            label: i18n('pass.name'),
            sortable: true,
          },
          {
            key: 'displayed_data.last_pass_date',
            label: i18n('pass.date'),
            sortable: true,
          },
          {
            key: 'displayed_data.last_login_date',
            label: 'Last login',
            sortable: true,
          },
          {
            key: 'is_verified',
            label: i18n('pass.verified'),
            sortable: true,
          },
          { key: 'remove', label: 'Remove' },
        ],
      },
    }
  },
  mounted: function () {
    this.fetchFoodsaverFromRegion(this.regionId)
  },
  methods: {
    getProfilUrl (userId) {
      return `/profile/${userId}`
    },
    async fetchFoodsaverFromRegion (regionId) {
      try {
        const foodsaver = await listRegionMembersDetailed(regionId)
        this.foodsaver = foodsaver.map(this.addNonApiAttributesToOneFoodsaver)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
    },
    addNonApiAttributesToOneFoodsaver (foodsaver) {
      const newAttributes = {
        checked: false,
        displayed_data: {
          last_login_date: new Date(foodsaver.last_login_datetime).toLocaleString(),
          last_pass_date: (foodsaver.last_pass_datetime === null) ? i18n('pass.none') : new Date(foodsaver.last_pass_datetime).toLocaleString(),
        },
      }
      return Object.assign(foodsaver, newAttributes)
    },
    deverificate (foodsaverId) {
      try {
        deverifyUser(foodsaverId)
        this.updateVerificationOfFoodsaverInTable(foodsaverId, false)
      } catch (e) {
        pulseError(e.message)
      }
    },
    verificate (foodsaverId) {
      try {
        verifyUser(foodsaverId)
        this.updateVerificationOfFoodsaverInTable(foodsaverId, true)
      } catch (e) {
        pulseError(e.message)
      }
    },
    updateVerificationOfFoodsaverInTable (foodsaverId, newVerificationStatus) {
      for (const foodsaver of this.foodsaver) {
        if (foodsaver.id === foodsaverId) {
          foodsaver.is_verified = newVerificationStatus
          foodsaver.displayed_data.last_pass_date = new Date().toLocaleString()
          break
        }
      }
    },
    removeFromRegion (foodsaverId) {
      try {
        removeMember(this.regionId, foodsaverId)
      } catch (e) {
        pulseError(e.message)
      }
    },
  },
}
</script>

<style scoped>

</style>
