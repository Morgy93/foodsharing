import { fetchFeatureToggle } from '@/api/featuretoggles'

export async function isFeatureToggleActive (featureToggleIdentifier) {
  const response = await fetchFeatureToggle(featureToggleIdentifier)
  return response.isActive
}
