<template>
  <div class="bootstrap">
    <tree
      ref="tree"
      :options="treeOptions"
      @node:selected="itemSelected"
    />
  </div>
</template>

<script>
import { listRegionChildren } from '@/api/regions'
import Tree from 'liquor-tree'

export default {
  components: { Tree },
  props: {
    // if not null, only these types of regions can be selected
    selectableRegionTypes: { type: Array, default: null },
  },
  data () {
    return {
      treeOptions: {
        checkbox: false,
        multiple: false,
        checkOnSelect: false,
        autoCheckChildren: false,
        parentSelect: false,
        fetchData: this.loadData,
      },
    }
  },
  methods: {
    // callback function that loads data for the tree
    async loadData (node) {
      const id = node.id === 'root' ? 0 : node.id

      const data = await listRegionChildren(id)
      return data.map(region => {
        return {
          id: region.id,
          text: region.name,
          isBatch: true,
          children: region.hasChildren ? [] : null,
          state: {
            selectable: this.selectableRegionTypes === null || this.selectableRegionTypes.includes(region.type),
          },
        }
      })
    },
    itemSelected (node) {
      this.$emit('change', {
        id: node.id,
        name: node.text,
      })
    },
  },
}
</script>
