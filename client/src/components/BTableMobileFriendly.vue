<template>
  <b-table v-bind="$attrs" v-on="$listeners" ref="table" :class="{'grid-layout': contentOverflow}">
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
    table: function () {
      return this.$refs.table.$el
    }
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
    }
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
      grid-gap: 0.15rem;
      background: var(--fs-color-background);
    }

    tr {
      background: var(--white);
      > td {
        display: flex;
      }

      > td::before {
        content: attr(data-th-label) ':';
        font-weight: bold;
        margin-right: auto;
      }
    }
  }
</style>
