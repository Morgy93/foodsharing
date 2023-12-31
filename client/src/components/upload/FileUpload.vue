<template>
  <div class="bootstrap ">
    <input
      ref="uploadElement"
      :accept="accept"
      name="imagefile[]"
      class="d-none"
      type="file"
      @change="onFileChange"
    >
    <div
      v-if="isImage"
      class="column align-items-center"
    >
      <div
        v-if="filename"
        class="mr-auto"
      >
        <div class="preview">
          <p>
            <img
              :src="filename"
              :alt="previewAlt"
            >
          </p>
        </div>
      </div>
      <div class="mr-auto">
        <div
          v-if="!filename"
          class="text-muted"
        >
          {{ $i18n('upload.no_image_yet') }}
        </div>
        <button
          class="btn btn-sm btn-primary"
          :class="{'disabledLoading': isLoading}"
          @click.prevent="openUploadDialog"
        >
          <span v-if="filename">{{ $i18n('upload.new_image') }}</span>
          <span v-else>{{ $i18n('upload.image') }}</span>
        </button>
      </div>
    </div>
    <div v-else>
      <div v-if="filename">
        {{ filenameWithoutPath }}
      </div>
      <div
        v-else
        class="text-muted"
      >
        {{ $i18n('upload.no_image_chosen') }}
      </div>
      <button
        class="btn btn-sm btn-primary"
        :class="{'disabledLoading': isLoading}"
        @click.prevent="openUploadDialog"
      >
        <span v-if="filename">{{ $i18n('upload.new_file') }}</span>
        <span v-else>{{ $i18n('upload.file') }}</span>
      </button>
    </div>

    <b-modal
      ref="upload-modal"
      :static="true"
      size="lg"
      :title="$i18n('upload.crop_dialog_title')"
      :cancel-title="$i18n('button.cancel')"
      :ok-title="$i18n('button.send')"
      modal-class="bootstrap"
      dialog-class="full-resize"
      hide-header-close
      @ok="cropImage"
    >
      <div class="resize-container">
        <vue-croppie
          ref="croppie"
          :boundary="boundary"
          :viewport="{ height: imgHeight, width: imgWidth }"
          :enable-resize="false"
        />
      </div>
    </b-modal>
  </div>
</template>

<script>
import VueCroppie from 'vue-croppie/src/VueCroppieComponent'
import { BModal, VBModal } from 'bootstrap-vue'
import { uploadFile } from '@/api/uploads'

export default {
  components: {
    'b-modal': BModal,
    VueCroppie,
  },
  directives: {
    'b-modal': VBModal,
  },
  props: {
    filename: {
      type: String,
      default: null,
    },
    isImage: {
      type: Boolean,
      default: false,
    },
    imgHeight: {
      type: Number,
      default: 0,
    },
    imgWidth: {
      type: Number,
      default: 0,
    },
  },
  data () {
    return {
      isLoading: false,
      newFilename: null,
      boundary: { height: this.imgHeight, width: this.imgWidth },
    }
  },
  computed: {
    filenameWithoutPath () {
      if (!this.filename) return false
      const splittedFilename = this.filename.split('/')
      return splittedFilename[splittedFilename.length - 1]
    },
    accept () {
      if (this.isImage) return 'image/*'
      else return ''
    },
    previewAlt () {
      return this.$i18n('upload.preview_image')
    },
  },
  methods: {
    openUploadDialog () {
      if (this.isLoading) return
      this.$refs.uploadElement.click()
    },
    onFileChange () {
      const file = this.$refs.uploadElement.files[0]
      this.resetFileInput(this.$refs.uploadElement)
      if (!file) return
      const filename = file.name
      const reader = new FileReader()
      this.isLoading = true
      if (this.isImage && this.imgHeight && this.imgWidth) {
        // Width/Height calculated:
        // For mobile use most of the window height, else take the image size
        const width = Math.min(window.innerWidth - 50, this.imgWidth * 1.1)
        const height = Math.min(window.innerHeight - 50, this.imgHeight + 100)
        this.boundary = { height, width }
        // Croppie needs to be destroyed, to take care of the new width/height :(
        try {
          this.$refs.croppie.destroy()
        } catch (err) {
          // If already destroyed, error will be thrown. Can't be checked before. :(
          // console.log('possible already destroyed')
        }
        reader.onload = (res) => {
          this.isLoading = false
          this.newFilename = filename
          this.openResizeDialog(res.target.result)
        }
        reader.readAsDataURL(file)
      } else {
        reader.onload = (res) => {
          this.uploadFile(filename, btoa(res.target.result))
        }
        reader.readAsBinaryString(file)
      }
    },
    resetFileInput (fileInputElement) {
      fileInputElement.type = 'text'
      fileInputElement.type = 'file'
    },
    async uploadFile (filename, data) {
      try {
        const res = await uploadFile(filename, data)
        this.$emit('change', res)
      } catch (err) {
        console.error(err)
      }
      this.isLoading = false
    },
    openResizeDialog (dataUrl) {
      this.$refs['upload-modal'].show()
      this.$refs.croppie.initCroppie()
      this.$refs.croppie.bind({
        url: dataUrl,
      })
    },
    cropImage () {
      this.isLoading = true
      this.$refs.croppie.result({
        type: 'base64',
      }, (output) => {
        this.uploadFile(this.newFilename, output.split('base64,')[1])
      })
    },
  },
}
</script>
<style lang="scss">
@import "~croppie/croppie.css";
</style>

<style lang="scss" scoped>

.preview {
  background-color: var(--fs-color-gray-500);
  padding: 0;
  img {
    width: 100%;
  }
}
</style>
<style lang="scss">
.full-resize {
  @media (min-width: 576px) {
    max-width: fit-content;
    margin: 1.75rem auto;
  }
}

</style>
