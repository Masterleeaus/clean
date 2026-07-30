<template>
  <div
    class="w-100 border rounded pa-3 cursor-pointer"
    style="position: relative; border-style: dashed!important;"
    @dragenter="handleEnter"
    @dragleave="handleLeave"
    @drop="handleDrop"
    @dragover.prevent
    @click="onButtonClick"
  >
    <div class="d-flex justify-space-between align-center">
      <div class="d-flex justify-start align-center">
        <v-avatar
          size="60"
          color="blue-grey-lighten-5"
          class="mr-4"
        >
          <UploadIcon :size="32" class="text-blue-grey-lighten-1"/>
        </v-avatar>
        <div class="text-body-1">
          <span v-text="!file ? label : name" />
        </div>
      </div>
      <div v-if="file">
        <v-btn
          variant="text"
          icon=""
          color="red-accent-3"
          density="comfortable"
          @click="clean"
        >
          <DeleteMiniIcon :size="24"/>
        </v-btn>
      </div>
    </div>
    <input
      type="file"
      ref="uploader"
      class="d-none"
      @change="onFileChange"
    >
  </div>
</template>

<script>
import UploadIcon from "@/components/icons/UploadIcon.vue";
import DeleteMiniIcon from "@/components/icons/DeleteMiniIcon.vue";

export default {
  name: 'Dropzone',
  components: {DeleteMiniIcon, UploadIcon},
  props: {
    label: {
      type: String,
      required: true
    }
  },
  data: () => ({
    dragging: false,
    file: null,
  }),
  computed: {
    name: function () {
      return this.file.name;
    }
  },
  watch: {
    file: function (val) {
      this.$emit("on-upload", val);
    },
  },
  methods: {
    handleLeave() {
      this.dragging = false;
    },
    handleEnter() {
      this.dragging = true;
    },
    clean(event) {
      event.stopPropagation();
      this.file = null;
      this.dragging = false;
      this.$refs.uploader.value = null;
    },
    handleDrop(event) {
      event.preventDefault();
      this.file = event.dataTransfer.files[0];
    },
    onButtonClick() {
      this.isSelecting = true;
      window.addEventListener('focus', () => {
        this.isSelecting = false
      }, {once: true});
      this.$refs.uploader.click();
    },
    onFileChange(e) {
      this.file = e.target.files[0];
    }
  },
  beforeMount() {

  }
};
</script>
