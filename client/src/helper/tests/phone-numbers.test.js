import assert from 'assert'
import PhoneNumber from '@/helper/phone-numbers'

describe('PhoneNumber.callableNumber', () => {
  describe('with number input', () => {
    it('should include leading country codes with +', () => {
      assert.strictEqual(PhoneNumber.callableNumber('+49 206 555 0100'), '+492065550100')
    })
    it('should ignore leading country codes with 00', () => {
      assert.strictEqual(PhoneNumber.callableNumber('0049 206 555 0100'), null)
    })
    it('should ignore numbers that are missing country codes', () => {
      assert.strictEqual(PhoneNumber.callableNumber('01760100045'), null)
    })
    it('... unless asked to display those as text', () => {
      assert.strictEqual(PhoneNumber.callableNumber('01760100045', true), '01760100045')
    })
  })
  describe('with text input', () => {
    it('should ignore empty input', () => {
      assert.strictEqual(PhoneNumber.callableNumber(''), null)
      assert.strictEqual(PhoneNumber.callableNumber('', true), null)
    })
    it('should ignore text-only input', () => {
      assert.strictEqual(PhoneNumber.callableNumber('CARGO BIKE MOUNTED'), null)
      assert.strictEqual(PhoneNumber.callableNumber('CARGO BIKE MOUNTED', true), null)
    })
  })
  describe('with mixed input', () => {
    it('should ignore whitespace', () => {
      assert.strictEqual(PhoneNumber.callableNumber(' +12065550100 '), '+12065550100')
    })
    it('should ignore separators and leading/trailing text', () => {
      assert.strictEqual(PhoneNumber.callableNumber('(+49 1760) 100-045 Car!'), '+491760100045')
    })
    // combinations like +49(0) are somewhat common for our old German phone data
    it('should ignore numbers with redundant area codes', () => {
      assert.strictEqual(PhoneNumber.callableNumber('+49(0) 206/555/0100'), '+492065550100')
    })
    // don't accidentally call service lines by parsing just a few numbers from text
    it('should ignore numbers that are too short to be valid', () => {
      assert.strictEqual(PhoneNumber.callableNumber('1 bike, 1 dolly, 2 cars'), null)
      assert.strictEqual(PhoneNumber.callableNumber('1 bike, 1 dolly, 2 cars', true), '1 bike, 1 dolly, 2 cars')
    })
  })
})
