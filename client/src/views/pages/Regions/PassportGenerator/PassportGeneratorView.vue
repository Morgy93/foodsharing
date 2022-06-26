<template>
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
                  <td>Name</td>
                  <td>Letzter Login</td>
                  <td>Verifiziert</td>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in foodsaver"
                  :key="user.id"
                >
                  <td>
                    <input type="checkbox">
                  </td>
                  <td>
                    <Avatar :is-sleeping="user.sleepStatus" />
                  </td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.avatar }}</td>
                  <td>{{ user.verified }}</td>
                  <td>{{ user.role }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </Container>
      </div>

      <div class="col-3">
        <Container title="Aktionen">
          <div class="card-body bg-white">
            <button class="btn btn-sm btn-block btn-primary">
              Alle markieren
            </button>
            <button class="btn btn-sm btn-block btn-primary">
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
import { listRegionMembers } from '@/api/regions'
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
        this.foodsaver = await listRegionMembersDetailed(regionId)
        console.log(this.foodsaver)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
    },
  },
}
</script>

<style scoped>

</style>
