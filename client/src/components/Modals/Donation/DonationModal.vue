<template>
  <div>
    <b-container>
      <div>
        <b-alert
          v-model="showTop"
          class="position-fixed fixed-top m-0 rounded-0"
          style="z-index: 2000;"
          variant="success"
          dismissible
        >
          <b-container class="mt-2">
            <h3>Spenden für foodsharing - wir brauchen Deine Unterstützung!</h3>
          </b-container>
          <b-container class="mt-2">
            <p>
              Du kannst uns <a
                href="https://spenden.foodsharing.de/"
                target="_self"
              >unter</a> mit einer Spende unterstützen.
            </p>
            <b-button variant="primary">
              Bisherige Spenden <b-badge variant="light">
                {{ donationLink.status.amount }} €
              </b-badge>
            </b-button>
          </b-container>
        </b-alert>
      </div>
    </b-container>
  </div>
</template>

<script>
export default {
  name: 'YourComponent',
  data () {
    return {
      showTop: true,
      donationLink: '',
    }
  },
  mounted () {
    this.fetchDonationLink()
  },
  methods: {
    async fetchDonationLink () {
      try {
        const response = await fetch('https://spenden.twingle.de/donation-status/amount-OsgX2t1ixdKwggsQa1pN6g%253D%253D?status=0')
        const data = await response.json() // Assuming the data is in JSON format
        this.donationLink = data
      } catch (error) {
        console.error('Error fetching donation link:', error)
      }
    },
  },
}
</script>

<style scoped>
.badge {
  font-size: 130%;
}
</style>
