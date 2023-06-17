import { get } from './base'
export async function fetchAllFeatureToggles () {
  return get('/featuretoggle/')
}

export async function fetchFeatureToggle (featureToggleIdentifier) {
  return get(`featuretoggle/${featureToggleIdentifier}`)
}
