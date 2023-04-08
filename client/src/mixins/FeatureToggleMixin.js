export default {
  methods: {
    async isFeatureFlagActive (featureFlagIdentifier) {
      const url = `/api/featureflags/${featureFlagIdentifier}`
      const response = await fetch(url)
      const data = await response.json()
      return data.isActive === true
    },
  },
}
