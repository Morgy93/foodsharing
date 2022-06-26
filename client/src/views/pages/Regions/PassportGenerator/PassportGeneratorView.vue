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
                  <td>Aktion</td>
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
                  <td>{{ user.name }} ({{ user.role_name }})</td>
                  <td>{{ user.displayed_data.last_pass_date }}</td>
                  <td>{{ user.displayed_data.last_login_date }}</td>
                  <td>{{ user.is_verified }}</td>
                  <td>
                    <button
                      type="button"
                      class="btn btn-sm btn-block btn-secondary"
                      data-toggle="modal"
                      data-target="#foodsaver-details-modal"
                    >
                      <i class="fa fa-eye" />
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
            <button class="btn btn-sm btn-block btn-danger">
              Aus Bezirk entfernen
            </button>
          </div>
        </Container>
      </div>
    </div>
  </section>
</template>

<script>
import Container from '@/components/Container/Container.vue'
import Avatar from '@/components/Avatar.vue'
import { listRegionMembersDetailed } from '@/api/regions'
import { pulseError } from '@/script'
import i18n from '@/i18n'

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
    }
  },
  mounted: function () {
    this.fetchFoodsaverFromRegion(this.regionId)
  },
  methods: {
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
  },
}
</script>

<style scoped>

</style>
