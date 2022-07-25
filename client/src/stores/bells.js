import Vue from 'vue'
import { getBellList, deleteBell, markBellsAsRead } from '@/api/bells'

export const store = Vue.observable({
  bells: [],
})

export const getters = {
  get: () => store.bells,
  getUnreadCount: () => store.bells.filter(b => !b.isRead).length,
}

export const mutations = {
  async fetch () {
    store.bells = await getBellList()
  },
  async delete (id) {
    const bell = store.bells.find(b => b.id === id)
    // this.$set(bell, 'isDeleting', true)
    try {
      await deleteBell(id)
      store.bells.splice(store.bells.indexOf(bell), 1)
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
    }

    await markBellsAsRead(ids)
  },
}

export default { store, getters, mutations }
