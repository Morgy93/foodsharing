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
                {{ formatCurrency(donationAmount.status.amount) }}
              </b-badge>
            </b-button>
            <b-button variant="primary">
              Spendenziel <b-badge variant="light">
                {{ formatCurrency(donationGoal) }}
              </b-badge>
            </b-button>
          </b-container>
          <b-progress
            v-if="donationAmount"
            class="mt-2 container"
            :max="donationGoal"
          >
            <b-progress-bar
              show-progress
              variant="secondary"
              :value="donationAmount.status.amount"
            />
          </b-progress>
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
      donationAmount: '',
      donationGoal: 50000,
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
        this.donationAmount = data
      } catch (error) {
        console.error('Error fetching donation link:', error)
      }
    },
    formatCurrency (amount) {
      return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(amount)
    },
  },
}
</script>

<style scoped>
.badge {
  font-size: 130%;
}
</style>
