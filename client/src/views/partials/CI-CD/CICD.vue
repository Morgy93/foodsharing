<template>
  <section class="cicd">
    <div
      v-for="(rule, idx) in rules"
      :key="idx"
      v-b-tooltip="rule"
      class="box"
      :style="`background-color: var(${rule})`"
    />
  </section>
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
              (def = rule.selectorText === ':root' ? [...def, ...Array.from(rule.style).filter(name => name.startsWith('--'))] : def), []),
          ]), [])
  },
}
</script>
<style lang="scss" scoped>
$size: 3rem;
.box {
  width: $size;
  height: $size;
}

.cicd {
  padding: 1rem;
  background-color: white;
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax($size,1fr));
  grid-gap: 2px;
}
</style>
