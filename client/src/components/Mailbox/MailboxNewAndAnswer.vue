<template>
  <Container
    :tag="showTitel"
    :title="showTitel"
    :toggle-visibility="true"
  >
    <div class="card bg-white">
      <b-row class="p-2">
        <b-col
          cols="12"
          md="2"
        >
          {{ $i18n('mailbox.sender') }}
        </b-col>
        <b-col
          cols="12"
          md="10"
        >
          <div>
            <b-form-select
              v-model="selectedMailbox[0]"
            >
              <b-form-select-option
                v-for="value in mailboxes"
                :key="value.id"
                :value="value.id"
              >
                {{ value.name }}
              </b-form-select-option>
            </b-form-select>
          </div>
        </b-col>
      </b-row>
      <b-row class="p-2">
        <b-col
          cols="12"
          md="2"
        >
          {{ $i18n('mailbox.recipient') }}
        </b-col>
        <b-col
          cols="12"
          md="10"
        >
          <b-form-tags
            v-model="emailTo"
            no-outer-focus
            :tag-validator="isValidEmail"
            :limit="100"
            separator=" ,;"
            size="sm"
            class="mb-2"
          >
            <template #default="{ tags, inputAttrs, inputHandlers, tagVariant, addTag, removeTag }">
              <b-input-group class="mb-2">
                <b-form-input
                  v-bind="inputAttrs"
                  :placeholder="$i18n('mailbox.tag_recipient_hint')"
                  class="form-control"
                  v-on="inputHandlers"
                />
                <b-input-group-append>
                  <b-button
                    v-if="!isMobile"
                    variant="outline-primary"
                    @click="addTag()"
                  >
                    {{ $i18n('mailbox.add') }}
                  </b-button>
                  <b-button
                    v-else
                    variant="outline-primary"
                    @click="addTag()"
                  >
                    +
                  </b-button>
                </b-input-group-append>
              </b-input-group>
              <div
                class="d-inline-block"
                style="font-size: 1.5rem;"
              >
                <b-form-tag
                  v-for="tag in tags"
                  :key="tag"
                  :title="tag"
                  :variant="tagVariant"
                  class="mr-1 badge-primary"
                  @remove="removeTag(tag)"
                >
                  {{ tag }}
                </b-form-tag>
              </div>
            </template>
          </b-form-tags>
        </b-col>
      </b-row>

      <b-row class="p-2">
        <b-col
          cols="12"
          md="2"
        >
          {{ $i18n('mailbox.subject') }}
        </b-col>
        <b-col
          cols="12"
          md="10"
        >
          <b-form-input
            v-model="subject"
          />
        </b-col>
      </b-row>

      <b-row>
        <b-col>
          <b-row class="p-2">
            <b-col md="2" />
            <b-col md="10">
              <div class="flex-container">
                <b-form-tags
                  v-model="attachmentFilesName"
                  no-outer-focus
                  size="sm"
                  class="mb-2"
                >
                  <template #default="{ tags, tagVariant, removeTag }">
                    <b-input-group class="mb-2">
                      <div
                        class="d-inline-block"
                        style="font-size: 1.5rem;"
                      >
                        <div v-if="!isMobile">
                          <b-form-tag
                            v-for="tag in tags"
                            :key="tag"
                            :title="tag"
                            :variant="tagVariant"
                            class="mr-1 badge-primary bFormTag"
                            @remove="removeTag(tag)"
                          >
                            {{ tag }}
                          </b-form-tag>
                        </div>
                        <b-form-tag
                          v-for="tag in tags"
                          v-else
                          :key="tag"
                          :title="tag"
                          :variant="tagVariant"
                          class="mr-1 badge-primary bFormTagMobile"
                          @remove="removeTag(tag)"
                        >
                          {{ tag }}
                        </b-form-tag>
                      </div>
                    </b-input-group>
                  </template>
                </b-form-tags>
                <input
                  id="files"
                  type="file"
                  multiple
                  class="hidden"
                  @change="storeFiles"
                >
                <label
                  v-if="isMobile"
                  for="files"
                  :title="$i18n('mailbox.search')"
                  class="btn btn-outline-primary btn-sm custom-label"
                >
                  <i class="fas fa-paperclip" />
                </label>
                <label
                  v-else
                  for="files"
                  :title="$i18n('mailbox.search')"
                  class="btn btn-outline-primary btn-sm custom-label"
                >
                  {{ $i18n('mailbox.search') }}
                </label>
              </div>
            </b-col>
          </b-row>
        </b-col>
      </b-row>

      <div class="p-2">
        <div v-if="answerMode">
          <b-form-textarea
            id="textarea"
            v-model="getMailBody"
            rows="12"
            max-rows="12"
          />
        </div>
        <div v-else>
          <b-form-textarea
            id="textarea"
            v-model="mailBody"
            rows="12"
            max-rows="12"
          />
        </div>
      </div>

      <b-row class="p-2">
        <b-col>
          <b-button
            size="sm"
            variant="outline-primary"
            :disabled="isBusy"
            @click="closeAndReturnToMailbox"
          >
            {{ $i18n('button.cancel') }}
          </b-button>
          <b-button
            size="sm"
            variant="primary"
            :disabled="isBusy || !(areAllEmailsValid && isSubjectValid)"
            @click="trySendEmail"
          >
            {{ $i18n('button.send') }}
          </b-button>
        </b-col>
      </b-row>
    </div>
  </container>
</template>

<script>
import Container from '@/components/Container/Container.vue'
import { sendEmail, setEmailProperties } from '@/api/mailbox'
import { uploadFile } from '@/api/uploads'
import { hideLoader, pulseError, pulseSuccess, showLoader } from '@/script'
import i18n from '@/helper/i18n'
import { store, MAILBOX_PAGE } from '@/stores/mailbox'

export default {
  components: { Container },
  props: {
    email: { type: Object, default: () => { } },
    mailboxes: { type: Array, default: () => { return [] } },
  },
  data () {
    return {
      isBusy: false,
      emailTo: [''],
      subject: '',
      mailBody: null,
      attachmentFilesName: [],
      attachmentFilesObjects: [],
      isMobile: false,
    }
  },
  computed: {
    showTitel () {
      return store.state.answerMode ? this.$i18n('mailbox.reply.full') : this.$i18n('mailbox.write')
    },
    displayedMailDate () {
      return this.$dateFormatter.format(this.email.time, {
        day: 'numeric',
        month: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
      })
    },
    answerMode () {
      return store.state.answerMode
    },
    mailboxAddress () {
      return this.selectedMailbox[1] + '@foodsharing.network'
    },
    selectedMailbox () {
      return store.state.selectedMailbox
    },
    getMailBody () {
      let value = null
      if (this.answerMode) {
        const mailFromAdress = '<' + this.email.from.address + '>'
        const mailFromAndAdress = this.email.from.name ? this.email.from.name + ' ' + mailFromAdress : mailFromAdress
        const mailFromAndDate = mailFromAndAdress + ' ' + this.$i18n('mailbox.for_quoting.has_from') + ' ' + this.displayedMailDate + ' ' + this.$i18n('mailbox.for_quoting.written_text') + ': \n\n'
        const replacedContent = '> ' + this.email.body.replace('\r', '\n')
        value = mailFromAndDate + replacedContent
      }
      return value
    },
    areAllEmailsValid () {
      if (this.emailTo.length === 0) {
        return false
      }

      for (const email of this.emailTo) {
        if (!this.isValidEmail(email)) {
          return false
        }
      }

      return true
    },
    isSubjectValid () {
      return this.subject.length >= 3
    },
  },
  watch: {
    attachmentFilesName (newFiles, oldFiles) {
      const removedFiles = oldFiles.filter(file => !newFiles.includes(file))
      removedFiles.forEach(file => {
        const index = this.attachmentFilesObjects.findIndex(obj => obj.name === file)
        if (index !== -1) {
          this.attachmentFilesObjects.splice(index, 1)
        }
      })
    },
    answerMode (newVal, oldVal) {
      if (newVal) {
        this.emailTo.push(this.email.from.address)
      } else if (!newVal && oldVal) {
        const index = this.emailTo.indexOf(this.email.from.address)
        if (index > -1) {
          this.emailTo.splice(index, 1)
        }
      }
    },
    email (newEmail, oldEmail) {
      if (newEmail && newEmail !== oldEmail) {
        this.subject = newEmail.subject
      }
    },
  },
  created () {
    window.addEventListener('resize', this.checkMobile)
    this.checkMobile()
    if (this.answerMode) {
      this.subject = this.email.subject
      this.emailTo.push(this.email.from.address)
    }
  },
  destroyed () {
    window.removeEventListener('resize', this.checkMobile)
  },
  methods: {
    checkMobile () {
      this.isMobile = window.innerWidth <= 768
    },
    isValidEmail (email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    },
    storeFiles (event) {
      const files = Array.from(event.target.files)
      files.forEach(file => {
        this.attachmentFilesName.push(file.name)
        this.attachmentFilesObjects.push(file)
      })
    },
    async trySetEmailStatus (state) {
      showLoader()
      this.isBusy = true
      try {
        await setEmailProperties(this.email.id, state)
        this.setIsReadState(state)
      } catch (e) {
        pulseError(i18n('error_unexpected'))
      }
      this.isBusy = false
      hideLoader()
    },
    async trySendEmail () {
      showLoader()
      this.isBusy = true
      let attachments = []
      try {
        // load the attachment files into memory and upload them
        const loadFilePromises = this.attachmentFilesObjects.map(this.loadFile)
        const uploadPromises = loadFilePromises.map(promise => promise.then(file => {
          return uploadFile(file.name, file.content)
        }))
        const responses = await Promise.all(uploadPromises)
        attachments = responses.map(response => {
          return {
            uuid: response.uuid,
            filename: response.filename,
          }
        })

        // send the email
        if (this.answerMode) {
          await sendEmail(this.selectedMailbox[0], [this.email.from.address], null, null, this.email.subject, this.email.body, attachments, this.email.id)
        } else {
          await sendEmail(this.selectedMailbox[0], this.emailTo, null, null, this.subject, this.mailBody, attachments, null)
        }
        this.closeAndReturnToMailbox()
        pulseSuccess(this.$i18n('mailbox.okay'))
      } catch (err) {
        const errorDescription = err.jsonContent ?? { message: '' }
        const errorMessage = `(${errorDescription.message ?? 'Unknown'})`
        pulseError(this.$i18n('mailbox.mailsend_unsuccess', { error: errorMessage }))
      }
      this.isBusy = false
      hideLoader()
    },
    /**
     * Returns a promise that loads a file into memory and encodes it as Base64.
     */
    loadFile (file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader()

        reader.onload = (event) => {
          const binaryStr = new Uint8Array(event.target.result)
          let base64 = ''
          binaryStr.forEach((byte) => {
            base64 += String.fromCharCode(byte)
          })
          base64 = window.btoa(base64)
          resolve({
            name: file.name,
            size: file.size,
            type: file.type,
            content: base64,
          })
        }

        reader.onerror = (error) => {
          reject(error)
        }

        reader.readAsArrayBuffer(file)
      })
    },
    toggleReadState () {
      this.trySetEmailStatus(!this.email.isRead)
    },
    setIsReadState (state) {
      return this.email.isRead
    },
    closeAndReturnToMailbox () {
      store.setPage(MAILBOX_PAGE.EMAIL_LIST)
    },
  },
}
</script>

<style scoped>
.badge-primary {
  background-color: darkgrey;
}

.btn-outline-primary:hover {
  color: unset;
  background-color: unset;
}

.bFormTagMobile {
  font-size: 0.7rem;
}

.bFormTag {
  font-size: 0.9rem;
}

.flex-container {
  display: flex;
}
</style>
