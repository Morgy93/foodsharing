<!-- input field that allows searching for users and shows suggestions -->
<template>
  <b-form-tags
    id="tags-with-dropdown"
    v-model="users"
    n-outer-focus
    class="mb-2"
  >
    <template #default="{ tags, disabled, addTag, removeTag }">
      <ul
        v-if="tags.length > 0"
        class="list-inline d-inline-block mb-2"
      >
        <li
          v-for="tag in tags"
          :key="tag"
          class="list-inline-item"
        >
          <b-form-tag
            :title="tag"
            :disabled="disabled"
            variant="info"
            @remove="onRemove(tag, {removeTag})"
          >
            {{ tag }}
          </b-form-tag>
        </li>
      </ul>

      <b-dropdown
        size="sm"
        variant="outline-secondary"
        block
        menu-class="w-100"
      >
        <template #button-content>
          <i
            class="fas fa-fw"
            :class="buttonIcon"
          />
          {{ $i18n('usersearch.select_tag') }}
        </template>
        <b-dropdown-form @submit.stop.prevent="() => {}">
          <b-form-group
            :label="$i18n('usersearch.search_tag')"
            label-for="tag-search-input"
            label-cols-md="auto"
            class="mb-0"
            label-size="sm"
            :description="placeholder"
            :disabled="disabled"
          >
            <b-form-input
              id="tag-search-input"
              v-model="userSearch"
              type="search"
              size="sm"
              autocomplete="off"
              @input="searchUser"
            />
          </b-form-group>
        </b-dropdown-form>
        <b-dropdown-divider />
        <b-dropdown-item-button
          v-for="option in possibleUsers.map(x => x.value)"
          :key="option"
          @click="onSelect({ option, addTag })"
        >
          {{ option }}
        </b-dropdown-item-button>
        <b-dropdown-text v-if="possibleUsers.length === 0">
          {{ $i18n('usersearch.no_tag_preset') }}
        </b-dropdown-text>
      </b-dropdown>
    </template>
  </b-form-tags>
</template>

<script>
import { searchUser } from '@/api/search'
import { getBasicUser } from '@/api/user'
import { pulseError } from '@/script'

export default {
  props: {
    value: { type: Array, default: () => [] },
    placeholder: { type: String, default: '' },
    buttonIcon: { type: String, required: true },
    buttonTooltip: { type: String, default: '' },
    /**
     * If not null, the search is restricted to this region.
     */
    regionId: { type: Number, default: null },
  },
  data () {
    return {
      loadingInitial: true,
      user: null,
      users: [],
      userObjects: [],
      userSearch: '',
      possibleUsers: [],
      loading: false,
      autoCompleteStyle: {
        inputWrapper: 'input-group',
        suggestions: 'position-absolute list-group',
        suggestItem: 'list-group-item',
      },
      controls: {
        selectionUp: [38, 33],
        selectionDown: [40, 34],
        select: [13, 36],
        showList: [40],
        hideList: [27, 35],
      },
    }
  },
  mounted () {
    this.loadingInitialValues()
  },
  methods: {
    filterSelectedUsers (userId) {
      return !this.userObjects.some(x => x.id === userId)
    },
    async searchUser (query) {
      this.loading = true

      // requests search results from the server
      let users = []
      const isNumber = /^\d+\.?\d*$/.test(query)
      if (query.length > 2 || isNumber) {
        try {
          users = await searchUser(query, this.regionId)
          const filteredIds = users.map(x => x.id).filter(this.filterSelectedUsers)
          users = users.filter(x => filteredIds.includes(x.id))
        } catch (e) {
          pulseError(this.$i18n('error_unexpected'))
        }
      } else {
        this.user = null
      }
      this.loading = false

      this.possibleUsers = users
    },
    onSelect ({ option, addTag }) {
      addTag(option)
      const newUser = this.possibleUsers.filter(x => x.value === option)[0]
      this.userSearch = ''
      this.possibleUsers = []
      this.userObjects.push(newUser)
      this.$emit('input', this.userObjects.map(x => x.id))
    },
    onRemove (option, { removeTag }) {
      removeTag(option)
      this.userObjects = this.userObjects.filter(x => option !== `${x.name} (${x.id})`)
      this.$emit('input', this.userObjects.map(x => x.id))
    },
    async loadingInitialValues () {
      this.userObjects = await Promise.all(this.value.map(async (id) => {
        const result = (getBasicUser(id))
        return result
      }))
      this.users = this.userObjects.map(x => `${x.name} (${x.id})`)
    },
  },
}
</script>

<style lang="scss" scoped>
.vue-simple-suggest::v-deep {
  .input-wrapper {
    border: 0;
    padding: 0;
  }

  .suggestions {
    z-index: 1000;
    margin: 0;
  }

  .suggest-item {
    display: inline-block;
    line-height: 1;
    max-width: 100%;
    text-overflow: ellipsis;
  }

  .suggest-item.hover {
    border: 1px solid var(--fs-color-secondary-500);
    background-color: var(--fs-color-secondary-500);
    color: white;
  }
}

.vue-simple-suggest.focus::v-deep {
  background-color: white !important;
  border: 0;
}

.vue-simple-suggest-enter-active.suggestions,
.vue-simple-suggest-leave-active.suggestions {
  transition: opacity .2s;
}

.vue-simple-suggest-enter.suggestions,
.vue-simple-suggest-leave-to.suggestions {
  opacity: 0 !important;
}
</style>
