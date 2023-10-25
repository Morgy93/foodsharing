import sinon from 'sinon'
import { mount } from '@vue/test-utils'
import { resetModules } from '>/utils'
import '@/vue'
import { setActivePinia, createPinia } from 'pinia'

const assert = require('assert')

function createMockStore () {
  return {
    added: '1983-04-10',
    address: 'Tanja-Oswald-Ring 08c 281',
    id: 15906,
    name: 'betrieb_Bader Hammer KG',
    region: 'Göttingen',
    status: 3,
  }
}

describe('StoreRegionList', () => {
  const sandbox = sinon.createSandbox()

  let storeList

  beforeEach(() => {
    setActivePinia(createPinia())
    storeList = require('./StoreRegionList').default
  })
  afterEach(() => {
    sandbox.restore()
    resetModules()
  })

  it('loads', () => {
    assert(storeList)
  })

  it('can render', () => {
    const regionName = 'Test Region Name'
    const wrapper = mount(storeList, {
      propsData: {
        regionName,
        stores: [createMockStore()],
      },
    })
    assert.notStrictEqual(wrapper.vm.$el.innerHTML.indexOf(regionName), -1)
  })
})
