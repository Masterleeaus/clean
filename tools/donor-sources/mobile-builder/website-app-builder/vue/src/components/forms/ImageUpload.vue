<template>
  <div
    class="d-flex justify-space-between align-center pa-4 w-100 border"
    style="border-radius: 6px"
  >
    <div class="d-flex justify-start align-center">
      <v-avatar
        color="blue-grey-lighten-5"
        :size="60"
        class="mr-4"
      >
        <v-progress-circular
          v-if="loading"
          indeterminate
          color="blue-grey-lighten-1"
          bg-color="blue-grey-lighten-5"
          size="26"
          width="2"
        ></v-progress-circular>
        <template v-else>
          <ImageIcon
            v-if="!src"
            :size="32"
            class="text-blue-grey-lighten-1"
          />
          <v-img
            v-else
            :src="src"
            alt=""
          ></v-img>
        </template>
      </v-avatar>
      <div>
        <div class="text-body-1 font-weight-medium">
          {{ title }}
        </div>
        <div class="text-body-2 text-blue-grey-darken-1">
          {{ subtitle }}
        </div>
      </div>
    </div>
    <v-btn
      density="comfortable"
      flat
      variant="tonal"
      color="primary"
      @click="onButtonClick"
    >
      {{ $tr('project', 'key_86') }}
    </v-btn>
    <input
      ref="uploader"
      class="d-none"
      type="file"
      :accept="!accept ? 'image/*' : accept"
      @change="onFileChanged"
    >
  </div>
</template>

<script>
import ImageIcon from "@/components/icons/ImageIcon.vue";

export default {
  name: 'ImageUpload',
  components: {ImageIcon},
  props: {
    loading: {
      type: Boolean
    },
    src: {
      type: String,
    },
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      required: true
    },
    accept: {
      type: String
    }
  },
  data: () => ({
    isSelecting: false
  }),
  watch: {

  },
  methods: {
    onButtonClick() {
      this.isSelecting = true;
      window.addEventListener('focus', () => {
        this.isSelecting = false
      }, {once: true});
      this.$refs.uploader.click();
    },
    onFileChanged(e) {
      const image = e.target.files[0];
      this.$emit("change-image", image);
    }
  },
  beforeMount() {

  }
};
</script>
