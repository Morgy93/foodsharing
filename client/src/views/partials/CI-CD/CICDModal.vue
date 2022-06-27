<template>
  <div
    id="styleGuideModal"
    tabindex="-1"
    class="modal fade"
    aria-labelledby="styleGuideModal"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Styleguide</h3>
        </div>
        <div
          class="modal-body"
        >
          <div>
            <h3>Colors</h3>
            <hr>
            <div class="grid">
              <div
                v-for="(rule, idx) in rules"
                :key="idx"
                data-show-as="tooltip"
                data-placement="bottom"
                :data-original-title="rule"
                :aria-label="rule"
                class="box copy"
                :style="`background-color: var(${rule})`"
                @click="copyToClipBoard(rule)"
              />
            </div>
          </div>
          <div class="mb-5">
            <h3>Icons</h3>
            <a href="https://fontawesome.com/v5/search?m=free&s=solid%2Cbrands">Font Awesome V5 (FREE)</a>
            <hr>
            <div class="flex">
              <i
                v-for="(rule, idx) in ['fa-spinner', 'fa-spinner fa-spin']"
                :key="idx"
                class="fas copy"
                :class="rule"
                @click="copyToClipBoard(rule)"
              />
            </div>
          </div>
          <div class="mb-5">
            <h3>Fonts</h3>
            <hr>
            <div>
              <h1>Headline #1, The quick brown fox jumps over the lazy dog</h1>
              <h2>Headline #2, The quick brown fox jumps over the lazy dog</h2>
              <h3>Headline #3, The quick brown fox jumps over the lazy dog</h3>
              <h4>Headline #4, The quick brown fox jumps over the lazy dog</h4>
              <h5>Headline #5, The quick brown fox jumps over the lazy dog</h5>
              <h6>Headline #6, The quick brown fox jumps over the lazy dog</h6>
              <hr>
              <p>Body, The quick brown fox jumps over the lazy dog</p>
              <p><a href="#">Link, The quick brown fox jumps over the lazy dog</a></p>
              <p><b>B, The quick brown fox jumps over the lazy dog</b></p>
              <p><strong>Strong, The quick brown fox jumps over the lazy dog</strong></p>
              <p><i>Italic, The quick brown fox jumps over the lazy dog</i></p>
              <p><em>EM, The quick brown fox jumps over the lazy dog</em></p>
              <p><small>Small, The quick brown fox jumps over the lazy dog</small></p>
              <p><code>Code, The quick brown fox jumps over the lazy dog</code></p>
            </div>
          </div>
          <div class="mb-5">
            <h3>Components</h3>
            <a href="https://getbootstrap.com/docs/4.6/getting-started/introduction/">Bootstrap V4.6</a>
            <hr>
            <div class="flex">
              <button
                v-for="style in ['primary', 'link', 'secondary', 'danger', 'info']"
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
                v-for="style in ['primary', 'link', 'secondary', 'danger', 'info']"
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
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-sm btn-light"
              data-dismiss="modal"
            >
              You can use the <code class="key">ESC</code> hotkey or click here to close this modal.
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      rules: [],
    }
  },
  async created () {
    this.rules = Array.from(document.styleSheets)
      .filter(sheet => sheet.href === null || sheet.href.startsWith(window.location.origin))
      .reduce(
        (acc, sheet) =>
          (acc = [...acc, ...Array.from(sheet.cssRules)
            .reduce((def, rule) =>
              (def = rule.selectorText === ':root' ? [...def, ...Array.from(rule.style).filter(name => name.startsWith('--') && name.includes('color'))] : def), []),
          ]), [])
  },
  methods: {
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
$size: 3rem;
.box {
  width: $size;
  height: $size;
  border-radius: var(--border-radius);

  &:hover {
    outline: 2px solid goldenrod;
  }
}

.copy {
  cursor: copy;
}

.cicd {
  padding: 1rem;
  background-color: white;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax($size,1fr));
  grid-gap: 2px;

  &-small {
    grid-template-columns: repeat(auto-fit, 1rem);
    grid-template-rows: repeat(auto-fit, 1rem);
  }
}

.flex {
  display: flex;
  flex-wrap: wrap;

  & > * {
    margin: 2px;
  }
}
</style>
