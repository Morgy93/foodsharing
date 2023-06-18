import { get, post } from './base'
export async function fetchAllFeatureToggles () {
  return get('/featuretoggle/')
}

export async function fetchFeatureToggle (featureToggleIdentifier) {
  return get(`/featuretoggle/${featureToggleIdentifier}`)
}

export async function switchFeatureToggleState (featureToggleIdentifier) {
  return post(`/featuretoggle/${featureToggleIdentifier}/toggle`)
}
