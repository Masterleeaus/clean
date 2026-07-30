<template>
  <PageBar :title="$tr('menu', 'key_43')"/>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="12" sm="12" cols="12" class="py-0">
        <ImageUpload
          class="mb-3"
          :src="null"
          :title="$tr('project', 'key_89')"
          :subtitle="$tr('project', 'key_88')"
          :loading="loadingUpload"
          @change-image="uploadIcon"
        />
        <div class="font-weight-medium text-subtitle-2 mb-5 mt-5">
          {{ $tr('project', 'key_90') }}
        </div>
        <v-list
          item-props
          density="comfortable"
        >
          <template v-for="(icon, index) in android">
            <v-list-item
              lines="one"
              density="default"
            >
              <template v-slot:prepend>
                <SquircleImage
                  :src="`${baseUrl}/android/${icon}?unix=${unix}`"
                  :size="28"
                  class="mr-5"
                />
              </template>
              <v-list-item-title>{{ icon }}</v-list-item-title>
              <template v-slot:append>
                <v-list-item-action>
                  <v-btn
                    icon=""
                    color="primary"
                    density="comfortable"
                    flat
                    variant="text"
                    :loading="loadingIndex === 'android_' + index"
                    @click="downloadIcon(icon, 'android', index)"
                  >
                    <DownloadIcon :size="18"/>
                  </v-btn>
                </v-list-item-action>
              </template>
            </v-list-item>
            <v-divider/>
          </template>
        </v-list>
        <div class="font-weight-medium text-subtitle-2 mb-5 mt-5">
          {{ $tr('project', 'key_91') }}
        </div>
        <v-list
          item-props
          density="comfortable"
        >
          <template v-for="(icon, index) in ios">
            <v-list-item
              lines="one"
              density="default"
            >
              <template v-slot:prepend>
                <SquircleImage
                  :src="`${baseUrl}/ios/${icon}?unix=${unix}`"
                  :size="28"
                  class="mr-5"
                />
              </template>
              <v-list-item-title>{{ icon }}</v-list-item-title>
              <template v-slot:append>
                <v-list-item-action>
                  <v-btn
                    icon=""
                    color="primary"
                    density="comfortable"
                    flat
                    variant="text"
                    :loading="loadingIndex === 'ios_' + index"
                    @click="downloadIcon(icon, 'ios', index)"
                  >
                    <DownloadIcon :size="18"/>
                  </v-btn>
                </v-list-item-action>
              </template>
            </v-list-item>
            <v-divider/>
          </template>
        </v-list>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import projectsService from "@/services/projects/projects.service";
import SquircleImage from "@/components/blocks/SquircleImage.vue";
import DownloadIcon from "@/components/icons/DownloadIcon.vue";
import ImageUpload from "@/components/forms/ImageUpload.vue";

export default {
  name: 'AssetsAppSettings',
  components: {ImageUpload, DownloadIcon, SquircleImage, ScreenLock, PageBar},
  data: () => ({
    loading: true,
    android: [],
    ios: [],
    baseUrl: "",
    unix: 0,
    isUpload: false,
    loadingIndex: undefined,
    loadingUpload: false
  }),
  watch: {

  },
  methods: {
    getIcons() {
      projectsService.getLaunchIcons(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.android = data.icons.android;
        this.ios = data.icons.ios;
        this.isUpload = data.icons.upload;
        this.baseUrl = data.url;
        this.unix = data.unix;
        this.loading = false;
      }).catch(e => {
        this.loading = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    downloadIcon(name, type, index) {
      this.loadingIndex = `${type}_${index}`
      projectsService.downloadIcon(this.$route.params.uid, name, type).then((res) => {
        const href = URL.createObjectURL(res.data);
        const link = document.createElement('a');
        link.href = href;
        link.setAttribute('download', name);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(href);
        this.loadingIndex = undefined;
      }).catch(e => {
        console.log("fail download icon", e);
        this.loadingIndex = undefined;
      });
    },
    uploadIcon(image) {
      if (!image) {
        return;
      }
      this.loadingUpload = true;
      projectsService.uploadIcon(this.$route.params.uid, image).then((res) => {
        const data = res.data;
        this.android = data.icons.android;
        this.ios = data.icons.ios;
        this.isUpload = data.icons.upload;
        this.baseUrl = data.url;
        this.unix = data.unix;
        this.loadingUpload = false;
        this.$emit("force_update_name");
      }).catch(e => {
        this.loadingUpload = false;
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getIcons();
  }
};
</script>
