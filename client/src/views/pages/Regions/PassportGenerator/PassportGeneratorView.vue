<template>
  <!-- eslint-disable -->
  <section class="bootstrap container-fluid">
    <div class="row">
      <div class="col-9">
        <Container title="Foodsaver">
          <div class="card-body bg-white">
            <table class="table">
              <thead>
                <tr>
                  <td>Ausgewählt</td>
                  <td>Bild</td>
                  <td>Name (Rolle)</td>
                  <td>Letzte Passgeneration</td>
                  <td>Letzter Login</td>
                  <td>Verifiziert</td>
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
        <Container title="Aktionen">
          <div class="card-body bg-white">
            <button class="btn btn-sm btn-block btn-primary" @click="() => {this.foodsaver.forEach(foodsaver => foodsaver.checked = true)}">
              Alle markieren
            </button>
            <button class="btn btn-sm btn-block btn-primary" @click="() => {this.foodsaver.forEach(foodsaver => foodsaver.checked = false)}">
              Alle unmarkieren
            </button>
            <button class="btn btn-sm btn-block btn-success">
              Pass erstellen
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
      test: 123,
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
        this.foodsaver = foodsaver.map(this.addAttributesToFoodsaver)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
    },
    addAttributesToFoodsaver (foodsaver) {
      const newAttributes = {
        checked: false,
        displayed_data: {
          last_login_date: new Date(foodsaver.last_login_datetime).toLocaleString(),
          last_pass_date: new Date(foodsaver.last_pass_datetime).toLocaleString(),
        },
      }
      return Object.assign(foodsaver, newAttributes)
    },
  },
}
</script>

<style scoped>

</style>
