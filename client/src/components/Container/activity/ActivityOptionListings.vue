<template>
  <div>
    <div class="alert alert-warning d-flex align-items-center">
      <i class="fas fa-info-circle mr-2" />
      <span> {{ $i18n('dashboard.activity_filter_info') }} </span>
    </div>
    <form id="activity-option-form">
      <fieldset
        v-for="listing in listings"
        :key="listing.name"
        :class="{disabledLoading: isLoading}"
      >
        <legend>{{ listing.name }}</legend>
        <div
          v-for="item in listing.items"
          :key="item.id"
          class="form-group mb-0"
        >
          <input
            :id="item.id"
            v-model="item.included"
            :name="listing.index"
            :value="item.id"
            type="checkbox"
          >
          <label :for="item.id">
            <img
              v-if="item.imageUrl"
              :alt="item.name"
              :src="item.imageUrl"
              height="24"
            >
            {{ item.name }}
          </label>
        </div>
        <label
          v-if="listing.items.length === 0"
          class="info-italic"
          v-html="$i18n('dashboard.empty_section', {type: listing.shortName})"
        />
      </fieldset>
      <hr>
      <div class="d-flex justify-content-between">
        <button
          class="btn btn-primary"
          @click.prevent="saveOptionListings"
          v-html="$i18n('dashboard.save_selection')"
        />
        <button
          class="btn btn-outline-danger"
          @click.prevent="$emit('close')"
          v-html="$i18n('button.cancel')"
        />
      </div>
    </form>
  </div>
</template>

<script>
import { getFilters, setFilters } from '@/api/dashboard'

export default {
  components: {},
  props: {},
  data () {
    return {
      listings: [],
      isLoading: true,
    }
  },
  computed: {
    filteredUpdates: function () {
      return this.updates.filter(
        a => this.displayedTypes.indexOf(a.type) !== -1,
      )
    },
  },
  async created () {
    this.listings = await getFilters()
    this.isLoading = false
  },
  methods: {
    async saveOptionListings () {
      this.isLoading = true
      await setFilters(this.listings)
      this.$emit('close')
      this.isLoading = false
      this.$emit('reload-data')
    },
  },
}
</script>

<style lang="scss" scoped>
fieldset:last-of-type .form-group:last-child {
  margin-bottom: 0;
}
</style>
