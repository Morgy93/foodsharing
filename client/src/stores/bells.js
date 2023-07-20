import Vue from 'vue'
import { deleteBell, getBellList, markBellsAsRead } from '@/api/bells'
import { getCache, getCacheInterval, setCache } from '@/helper/cache'

const bellsRateLimitInterval = 60000 // 1 minute in milliseconds
const cacheRequestName = 'bells'

export const store = Vue.observable({
  bells: [],
})

export const getters = {
  get: () => store.bells,
  getUnreadCount: () => store.bells.filter(b => !b.isRead).length,
}

export const mutations = {
  async fetch (withoutCache = false) {
    try {
      if (await getCacheInterval(cacheRequestName, bellsRateLimitInterval) || withoutCache) {
        store.bells = await getBellList()

        await setCache(cacheRequestName, store.bells)
      } else {
        store.bells = await getCache(cacheRequestName)
      }
    } catch (e) {
      console.error('Error fetching bells:', e)
    }
  },
  async delete (id) {
    const bell = store.bells.find(b => b.id === id)
    // this.$set(bell, 'isDeleting', true)
    try {
      await deleteBell(id)
      store.bells.splice(store.bells.indexOf(bell), 1)
      await setCache(cacheRequestName, store.bells)
      const withoutCache = true
      await this.fetch(withoutCache)
    } catch (err) {
      console.log(err)
      // this.$set(bell, 'isDeleting', false)
      throw err
    }
  },
  markAsRead (bell) {
    const bellsToMarkAsRead = this.allBellsWithSameHref(bell)
    this.markBells(bellsToMarkAsRead)
  },
  markNewBellsAsRead () {
    const bellsToMarkAsRead = store.bells
    this.markBells(bellsToMarkAsRead)
  },
  allBellsWithSameHref (bell) {
    return store.bells.filter(b => b.href === bell.href)
  },
  async markBells (bellsToMarkAsRead) {
    const ids = []

    for (const b of bellsToMarkAsRead) {
      b.isRead = true
      ids.push(b.id)
      await setCache(cacheRequestName, store.bells)
    }

    await markBellsAsRead(ids)
  },
}

export default { store, getters, mutations }
