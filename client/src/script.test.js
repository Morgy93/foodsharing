import sinon from 'sinon'
import assert from 'assert'
import { sleep, resetModules } from '>/utils'

describe('script', () => {
  const sandbox = sinon.createSandbox()

  let browser
  let script
  let mockBrowser
  let server

  beforeEach(() => {
    server = sinon.createFakeServer()

    browser = require('@/browser')
    script = require('@/script')

    mockBrowser = sandbox.mock(browser)
  })

  afterEach(() => {
    mockBrowser.verify()
    server.restore()
    sandbox.restore()
    resetModules()
  })

  describe('on mobile', () => {
    beforeEach(() => sandbox.stub(browser, 'isMob').returns(true))

    describe('isMob', () => {
      it('works', () => {
        assert.strictEqual(script.isMob(), true)
      })
    })
  })

  describe('on desktop', () => {
    beforeEach(() => sandbox.stub(browser, 'isMob').returns(false))

    it('is not mobile!', () => {
      assert.strictEqual(script.isMob(), false)
    })

    it('can initialize', () => {
      script.initialize()
    })

    describe('pulse', () => {
      let info
      let success
      let error
      beforeEach(() => {
        document.body.innerHTML = `
            <div class="pulse-msg ui-shadow ui-corner-all" id="pulse-error" style="display:none;"></div>
            <div class="pulse-msg ui-shadow ui-corner-all" id="pulse-info" style="display:none;"></div>
            <div class="pulse-msg ui-shadow ui-corner-all" id="pulse-success" style="display:none;"></div>
        `

        info = document.getElementById('pulse-info')
        success = document.getElementById('pulse-success')
        error = document.getElementById('pulse-error')
      })
      afterEach(() => {
        for (const el of [info, success, error]) {
          el.style.display = 'none'
        }
      })
      it('can show info', () => {
        const message = 'a nice info message'
        script.pulseInfo(message, { timeout: 0 })
        assert.strictEqual(info.innerHTML, message)
      })

      it('can show success', () => {
        const message = 'a nice success message'
        script.pulseSuccess(message, { timeout: 0 })
        assert.strictEqual(success.innerHTML, message)
      })

      it('can show error', () => {
        const message = 'a nice error message'
        script.pulseError(message, { timeout: 0 })
        assert.strictEqual(error.innerHTML, message)
      })

      it('will be hidden after a timeout', async () => {
        const message = 'a nice message'
        assert.strictEqual(info.style.display, 'none')
        script.pulseInfo(message, { timeout: 0 })
        assert(['block', ''].includes(info.style.display))
        await sleep(20)
        assert.strictEqual(info.style.display, 'none')
      })
    })
  })
})
