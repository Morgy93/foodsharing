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
    /**
     * Returns the node that corresponds to the region id, or null if no such node is in the tree.
     */
    findRegionById (regionId) {
      const found = this.$refs.tree.find(node => node.id === regionId)
      return found.length > 0 ? found[0].select() : null
    },
    /**
     * Selects the region with the given id.
     */
    selectRegion (regionId) {
      this.findRegionById(regionId)?.select()
    },
    /**
     * Makes the node corresponding to the region reload its children.
     */
    async updateNode (regionId) {
      const node = this.findRegionById(regionId)
      if (node) {
        // remove all children
        const children = Array.from(node.children)
        for (let i = 0; i < children.length; ++i) {
          node.removeChild(children[i])
        }

        // reload children
        await node.tree.loadChildren(node)
        node.expand()
      }
    },
  },
}
</script>
