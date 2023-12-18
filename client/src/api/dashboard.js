import { get, patch } from './base'

export async function getUpdates (pagenumber) {
  return (await get(`/activities/updates?page=${pagenumber}`)).updates
}

export async function getFilters () {
  return (await get('/activities/filters'))
}

export async function setFilters (options) {
  const excluded = []
  for (const optionId in options) {
    options[optionId].items = options[optionId].items.filter((a) => { return !a.included })
    for (const item in options[optionId].items) {
      excluded.push({
        index: options[optionId].index,
        id: options[optionId].items[item].id,
      })
    }
  }
  return patch('/activities/filters', {
    excluded: excluded,
  })
}
