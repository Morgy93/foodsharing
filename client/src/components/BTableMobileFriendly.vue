<template>
  <b-table v-bind="attributes" v-on="$listeners" ref="table" :class="{'grid-layout': contentOverflow}">
    <slot v-for="(_, name) in $slots" :name="name" :slot="name" />
    <template v-for="(_, name) in $scopedSlots" v-slot:[name]="slotData">
      <slot :name="name" v-bind="slotData" />
    </template>
  </b-table>
</template>

<script>
import { defineComponent } from 'vue'
import { BTable } from 'bootstrap-vue'

export default defineComponent({
  name: 'BTableMobileFriendly',
  comments: { BTable },
  created () {
    console.log(this)
  },
  mounted () {
    console.log(this.table)
    const resizeObserver = new ResizeObserver(this.onTableResize)
    resizeObserver.observe(this.table)
    this.onTableResize()
  },
  data: () => ({
    contentOverflow: false,
  }),
  computed: {
    attributes: function () {
      const attrs = this.$attrs
      if (attrs.fields) attrs.fields = attrs.fields.map(this.addTdAttr)
      return attrs
    },
    table: function () {
      return this.$refs.table.$el
    },
  },
  methods: {
    doesElementOverflow (element) {
      return element.clientWidth < element.scrollWidth
    },
    onTableResize () {
      this.contentOverflow = false
      this.$nextTick(() => {
        this.contentOverflow = this.doesElementOverflow(this.table)
      })
    },
    addTdAttr: field => ({...field, tdAttr: {"data-th-label": field.label} }),
  }
})
</script>

<style lang="scss">
  .grid-layout {
    thead {
      display: none;
    }

    tbody {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr)); // 18rem is hardcoded for minimum store item / Idk how to make this flexible
      grid-column-gap: 0.15rem;
      background: var(--fs-color-gray-200);
    }

    tr {
      background: var(--white);
      > td {
        display: flex;
      }

      > td {
        //display: none;
        &::before {
          content: attr(data-th-label) ':';
          margin-right: auto;
        }
        &:first-child {
          display: initial;
          &::before {
            font-weight: bold;
          }
        }
        &.expand {
          display: initial;
        }
      }
    }
  }
</style>
