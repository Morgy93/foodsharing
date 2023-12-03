<template>
  <div>
    <b-container>
      <b-collapse
        id="banner-collapse"
        :visible="!isBannerCollapsed"
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
      </b-collapse>
      <b-button
        v-if="!isBannerCollapsed"
        variant="outline-primary"
        class="mb-2"
        @click="toggleBanner"
      >
        <i class="fas fa-times" />
      </b-button>
    </b-container>
  </div>
</template>

<script>
export default {
  name: 'YourComponent',
  data () {
    return {
      isBannerCollapsed: false,
      donationLink: '',
    }
  },
  mounted () {
    this.fetchDonationLink()
  },
  methods: {
    toggleBanner () {
      this.isBannerCollapsed = !this.isBannerCollapsed
    },
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
