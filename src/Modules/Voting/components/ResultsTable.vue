<template>
  <div class="bootstrap">
    <div class="my-1 mb-2">
      <b>{{ $i18n('poll.results.number_of_votes') }}</b>: {{ numVotes }}
    </div>

    <b-table
      v-if="numValues===1"
      :fields="tableFields"
      :items="options"
      primary-key="optionIndex"
      small
      hover
      responsive
      striped
      sort-by="optionText"
      :sort-desc="false"
    >
      <template #head(value1)>
        {{ $i18n('poll.results.votes') }}
      </template>
    </b-table>

    <b-table
      v-else-if="numValues===3"
      :fields="tableFields"
      :items="options"
      small
      hover
      responsive
      striped
    >
      <template #head(value1)>
        <i class="fas fa-thumbs-up" />
      </template>
      <template #head(value0)>
        <i class="fas fa-meh" />
      </template>
      <template #head(value-1)>
        <i class="fas fa-thumbs-down" />
      </template>
      <template #head(sum)="row">
        {{ row.label }} (<i class="fas fa-thumbs-up" /> = +1, <i class="fas fa-thumbs-down" /> = -1)
      </template>
    </b-table>

    <b-table
      v-else-if="numValues===7"
      :fields="tableFields"
      :items="options"
      small
      hover
      responsive
      striped
    />
  </div>
</template>

<script>

import { BTable } from 'bootstrap-vue'

export default {
  components: { BTable },
  props: {
    options: {
      type: Array,
      required: true,
    },
    numVotes: {
      type: Number,
      required: true,
    },
  },
  computed: {
    numValues () {
      return Object.entries(this.options[0].values).length
    },
    tableFields () {
      const result = [
        {
          key: 'text',
          sortable: true,
          sortByFormatted: 'true',
          label: this.$i18n('poll.results.option_text'),
          class: 'align-left',
        },
      ]

      const entries = Object.entries(this.options[0].values).sort(function (a, b) {
        return b[0] - a[0]
      })
      entries.forEach(v => {
        result.push({
          key: 'value' + v[0],
          label: v[0],
          sortable: true,
          sortByFormatted: 'true',
          formatter: (value, key, item) => {
            return item.values[v[0]]
          },
        })
      })

      if (this.numValues > 1) {
        result.push({
          key: 'sum',
          label: this.$i18n(this.numValues === 7 ? 'poll.results.average' : 'poll.results.sum'),
          sortable: true,
          sortByFormatted: 'true',
          class: 'text-center',
          formatter: (value, key, item) => {
            let sum = this.sumVotes(item)
            if (this.numValues === 7) {
              sum = Math.round(sum / this.numVotes * 100) / 100
            }
            return sum
          },
        })
      }
      return result
    },
  },
  methods: {
    sumVotes (option) {
      let sum = 0
      for (const v in option.values) {
        sum += v * option.values[v]
      }
      return sum
    },
  },
}
</script>
