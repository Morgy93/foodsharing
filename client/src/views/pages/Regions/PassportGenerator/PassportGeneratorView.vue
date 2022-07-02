<template>
  <!-- eslint-disable -->
  <section class="bootstrap container-fluid">
    <div class="row">
      <div class="col-9">
        <Container :title="this.main_container_title">
          <div class="card-body bg-white">
            <table class="table">
              <thead>
                <tr>
                  <td></td>
                  <td>{{ $i18n('pass.photo') }}</td>
                  <td>{{ $i18n('pass.name') }}</td>
                  <td>{{ $i18n('pass.date') }}</td>
                  <td>Letzter Login</td>
                  <td>{{ $i18n('pass.verified') }}</td>
                  <td>Actions</td>
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
                    <button class="btn btn-block btn-danger" v-b-modal.remove-from-region-modal
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
