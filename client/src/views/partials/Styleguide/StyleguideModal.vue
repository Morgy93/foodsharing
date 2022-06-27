<template>
  <div
    id="styleGuideModal"
    tabindex="-1"
    class="modal fade"
    aria-labelledby="styleGuideModal"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h1>Styleguide</h1>
        </div>
        <div
          class="modal-body row"
        >
          <div class="col-12 mb-5">
            <h2>Colors</h2>
            <hr>
            <div class="flex">
              <div
                v-for="(color, idx) in cssColorRules"
                :key="idx"
                class="col col-6"
              >
                <small
                  v-html="color[0].type.name.toUpperCase()"
                />
                <div class="flex">
                  <div
                    v-for="({type, rule, base}) in color"
                    :key="rule"
                    class="box"
                    :class="{ 'base': base }"
                    :style="`background-color: var(${rule})`"
                    :data-content="type.step"
                    @click="copyToClipBoard(rule)"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="mb-5">
              <h2>Icons <a href="https://fontawesome.com/v5/search?m=free&s=solid%2Cbrands">(Font Awesome V5 [FREE])</a></h2>
              <hr>
              <div class="flex">
                <i
                  v-for="(rule, idx) in ['fa-spinner', 'fa-spinner fa-spin']"
                  :key="idx"
                  class="icon fas copy"
                  :class="rule"
                  @click="copyToClipBoard(rule)"
                />
              </div>
            </div>
            <div class="mb-5">
              <h2>
                <a
                  href="https://getbootstrap.com/docs/4.6/getting-started/introduction/"
                  v-html="'Bootstrap 4.6'"
                />
              </h2>
              <hr>
              <div class="flex">
                <button
                  v-for="style in ['primary','light', 'link', 'secondary', 'danger', 'info']"
                  :key="`btn-${style}`"
                  class="copy btn"
                  :class="`btn-${style}`"
                  @click="copyToClipBoard(`btn btn-${style}`)"
                  v-html="`btn-${style}`"
                />
              </div>
              <div class="flex">
                <button
                  v-for="style in ['primary', 'secondary', 'danger', 'info']"
                  :key="`btn-outline-${style}`"
                  class="copy btn"
                  :class="`btn-outline-${style}`"
                  @click="copyToClipBoard(`btn btn-outline-${style}`)"
                  v-html="`btn-outline-${style}`"
                />
              </div>
              <div class="flex">
                <button
                  v-for="style in ['primary','light', 'link', 'secondary', 'danger', 'info']"
                  :key="`btn-sm-${style}`"
                  class="copy btn btn-sm"
                  :class="`btn-${style}`"
                  @click="copyToClipBoard(`btn btn-sm btn-${style}`)"
                  v-html="`btn-sm btn-${style}`"
                />
              </div>
              <div class="flex">
                <button
                  v-for="style in ['primary', 'secondary', 'danger', 'info']"
                  :key="`btn-sm-outline-${style}`"
                  class="copy btn btn-sm"
                  :class="`btn-outline-${style}`"
                  @click="copyToClipBoard(`btn btn-sm btn-outline-${style}`)"
                  v-html="`btn-sm btn-outline-${style}`"
                />
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6">
            <div class="mb-5">
              <h2>Fonts</h2>
              <hr>
              <div v-html="generateFontRules()" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-sm btn-light"
            data-dismiss="modal"
            v-html="$i18n('globals.modal.close')"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      cssColorRules: [],
      cssFontRules: [
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'p',
        'span',
        'a',
        'b',
        'strong',
        'i',
        'em',
        'small',
        'code',
        'mark',
        'ins',
        'del',
        'sup',
        'sub',
        'blockquote',
        'pre',
        'ul',
        'ol',
        'dl',
      ],
    }
  },
  created () {
    const cssColorRules = Array.from(document.styleSheets)
      .filter(sheet => sheet.href === null || sheet.href.startsWith(window.location.origin))
      .reduce((acc, sheet) => (acc = [...acc, ...Array.from(sheet.cssRules)]), [])
      .filter(rule => rule.selectorText === ':root')
      .reduce((acc, sheet) => (acc = [...acc, ...Array.from(sheet.style)]), [])
      .filter(name => name.startsWith('--') && name.includes('color'))
      .map((rule) => ({ type: this.extractColor(rule), rule }))
    this.cssColorRules = this.chunkArray(cssColorRules, 9)
  },
  methods: {
    generateFontRules () {
      return this.cssFontRules
        .map((font) => {
          const str = []
          str.push('<div>')
          if (font === 'a') {
            str.push(`<${font} href="#">`)
          } else {
            str.push(`<${font}>`)
          }

          if (['ul', 'ol'].includes(font)) {
            str.push(`<li>${font} li, The quick brown fox jumps over ...</li>`)
            str.push(`<li>${font} li, The quick brown fox jumps over ...</li>`)
            str.push(`<li>${font} li, The quick brown fox jumps over ...</li>`)
            str.push(`<li>${font} li, The quick brown fox jumps over ...</li>`)
          } else if (font === 'dl') {
            str.push(`<dt>${font} dt, The quick brown fox jumps over ...</dt>`)
            str.push(`<dd>${font} dd, The quick brown fox jumps over ...</dd>`)
            str.push(`<dt>${font} dt, The quick brown fox jumps over ...</dd>`)
            str.push(`<dd>${font} dd, The quick brown fox jumps over ...</dd>`)
          } else {
            str.push(`${font}, The quick brown fox jumps over ...`)
          }
          str.push(`</${font}>`)
          str.push('</div>')
          return str.join('')
        })
        .join('')
    },
    extractColor (color) {
      const regex = /--fs-color-(.+)-(\d+)/gm
      const match = regex.exec(color) || ['', 'others', color]
      console.log(match)
      return match ? { name: match[1], step: match[2] } : 'OTHERS'
    },
    chunkArray (myArray, chunkSize) {
      const arrayLength = myArray.length
      const tempArray = []
      for (let index = 0; index < arrayLength; index += chunkSize) {
        tempArray.push(myArray.slice(index, index + chunkSize))
      }
      return tempArray
    },
    copyToClipBoard (text) {
      navigator.clipboard.writeText(text).then(function () {
        console.log('Async: Copying to clipboard was successful!')
      }, function (err) {
        console.error('Async: Could not copy text: ', err)
      })
    },
  },
}
</script>
<style lang="scss" scoped>
.box {
  width: 30%;
  height: 1.25rem;
  border-radius: var(--border-radius);
  margin: 4px;
  cursor: copy;
  display: flex;
  align-items: center;
  justify-content: center;

  &:after {
    content: attr(data-content);
    mix-blend-mode: difference;
    color: white;
    font-size: 0.7rem;
  }

  &:hover {
    outline: 2px solid var(--fs-color-dark);
  }
}

.btn {
  margin: .1rem;
}

.col:not(:last-child) {
  padding-right: 0;
}

.flex {
  display: flex;
  flex-flow: wrap;

  &:not(:last-child) {
    margin-bottom: 1rem;
    margin-right: 1rem;
  }
}
</style>
