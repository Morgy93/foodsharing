<template>
  <b-modal
    ref="languageChooserModal"
    :title="$i18n('language_chooser.title')"
    :cancel-title="$i18n('button.cancel')"
    :ok-title="$i18n('language_chooser.choose_button')"
    modal-class="bootstrap"
    header-class="d-flex"
    content-class="pr-3 pt-3"
    @ok="changeLanguage"
  >
    {{ $i18n('language_chooser.content') }}
    <div
      v-if="loading"
      class="loader-container mx-auto"
    >
      <b-img
        center
        src="/img/469.gif"
      />
    </div>
    <b-form-select
      v-else
      v-model="language"
      :options="languages"
      text="Dropdown Button"
      class="m-md-2"
    />
  </b-modal>
</template>

<script>
import { pulseError } from '@/script'
import { getLocale, setLocale } from '@/api/locale'
import { getters, mutations } from '@/stores/languageChooser'

export default {
  name: 'LanguageChooser',
  data () {
    return {
      language: null,
      languages: [
        { value: 'de', text: 'Deutsch' },
        { value: 'en', text: 'English' },
        { value: 'fr', text: 'Français' },
        { value: 'it', text: 'Italiano' },
        { value: 'nb_NO', text: 'Norsk (Bokmål)' },
      ],
      loading: true,
    }
  },
  computed: {
    isShown () {
      return getters.get()
    },
  },
  watch: {
    isShown (isShown) {
      if (isShown) {
        this.show()
      }
    },
  },
  methods: {
    show () {
      this.$refs.languageChooserModal.show()
      this.getLanguage()
    },
    async getLanguage () {
      this.loading = true

      try {
        this.language = await getLocale()
        mutations.hide()
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
      }

      this.loading = false
    },
    async changeLanguage () {
      try {
        mutations.hide()
        await setLocale(this.language)
        location.reload()
      } catch (e) {
        pulseError(this.$i18n('error_unexpected'))
      }
    },
  },
}
</script>
