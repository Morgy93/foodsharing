<template>
  <div class="mobile-friendly-table">
    <button type="button" @click="setLabelOnTdElements">set labels</button>
    <slot />
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'MobileFriendlyTable',
  mounted () {
    this.setLabelOnTdElements()
  },
  methods: {
    setLabelOnTdElements () {
      const tr = this.$el.querySelector('thead > tr')
      const labels = Array.from(tr.children).map(child => child.children[0].innerText)
      const rows = this.$el.querySelectorAll('tbody > tr')
      rows.forEach(row => {
        const tds = Array.from(row.children)
        if (tds.length !== labels.length) throw Error('table header/label mismatch')
        console.log(tds)
        for (const index in tds) {
          const td = tds[index]
          console.log(td.children.length, tds[index], labels[index])
          td.dataset.label = labels[index]
          if (!td.children.length) {
            // this.wrapTextInSpan(td)
          }
        }
      })
    },
    wrapTextInSpan (element) {
      const span = document.createElement('span')
      span.innerText = element.innerText
      element.innerText = ''
      element.append(span)
    },
  },
})
</script>

<style lang="scss">
  .mobile-friendly-table {
    thead {
      display: none;
    }
    tr {
      > td {
        display: flex;
        > * {
          //flex-grow: 3;
          text-align: center;
        }
      }
      > td::before {
        content: attr(data-label);
        font-weight: bold;
        width: 25%;
      }
    }
  }
</style>
