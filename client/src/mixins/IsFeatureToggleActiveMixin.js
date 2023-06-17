export default {
  methods: {
    async isFeatureToggleActive (featureToggleIdentifier) {
      const url = `/api/featureflags/${featureToggleIdentifier}`
      const response = await fetch(url)
      const data = await response.json()
      return data.isActive === true
    },
  },
}
