<template>
  <PageBar :title="$tr('menu', 'key_42')"/>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_71')"
          variant="outlined"
          density="comfortable"
          :items="backgrounds"
          v-model="background_mode"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <ColorInput
          :label="$tr('project', 'key_72')"
          v-model="color"
          @change="(val) => this.color = val"
        />
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_78')"
          variant="outlined"
          density="comfortable"
          v-model="tagline"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_75')"
          variant="outlined"
          density="comfortable"
          :items="themes"
          v-model="theme"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_79')"
          variant="outlined"
          density="comfortable"
          v-model="delay"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_82')"
          variant="outlined"
          density="comfortable"
          :items="logos"
          v-model="use_logo"
        ></v-select>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0 mt-5">
        <div class="font-weight-medium text-subtitle-2 mb-5">
          {{ $tr('project', 'key_83') }}
        </div>
        <ImageUpload
          class="mb-3"
          :src="background"
          :title="$tr('project', 'key_80')"
          :subtitle="$tr('project', 'key_84')"
          :loading="isUploadBackground"
          @change-image="uploadBackground"
        />
        <ImageUpload
          class="mb-3"
          :src="logo"
          :title="$tr('project', 'key_81')"
          :subtitle="$tr('project', 'key_87')"
          :loading="isUploadedLogo"
          @change-image="uploadLogo"
        />
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateSplash">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import ColorInput from "@/components/forms/ColorInput.vue";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import ImageUpload from "@/components/forms/ImageUpload.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'AdminSplashscreenAppSettings',
  components: {ImageUpload, FixedFooter, ColorInput, ScreenLock, PageBar},
  data: () => ({
    loading: true,
    background_mode: 0,
    color: "#F44336",
    tagline: "",
    theme: 0,
    delay: 3,
    use_logo: 0,
    background: null,
    logo: null,
    isUpdate: false,
    isUploadBackground: false,
    isUploadedLogo: false
  }),
  computed: {
    backgrounds: function () {
      return [
        {
          title: this.$tr('project', 'key_72'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_73'),
          value: 1
        },
      ];
    },
    themes: function () {
      return [
        {
          title: this.$tr('project', 'key_76'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_77'),
          value: 1
        },
      ];
    },
    logos: function () {
      return [
        {
          title: this.$tr('project', 'key_44'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_43'),
          value: 1
        },
      ];
    }
  },
  watch: {

  },
  methods: {
    getSplashDetail() {
      this.loading = true;
      adminProjectsService.getSplashscreenSettings(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.background_mode = data.background_mode;
        this.color = data.color;
        this.tagline = data.tagline;
        this.theme = data.theme;
        this.delay = data.delay;
        this.use_logo = data.use_logo;
        this.background = data.background;
        this.logo = data.logo;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    updateSplash() {
      this.isUpdate = true;
      adminProjectsService.updateSplashScreenSettings(this.$route.params.uid, {
        background_mode: this.background_mode,
        color: this.color,
        tagline: this.tagline,
        delay: this.delay,
        theme: this.theme,
        use_logo: this.use_logo
      }).then(() => {
        this.isUpdate = false;
        this.$refs.footer.showSuccessAlert();
        this.$emit("preview-update");
      }).catch(e => {
        this.isUpdate = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    uploadBackground(image) {
      if (!image) {
        return;
      }
      this.isUploadBackground = true;
      adminProjectsService.uploadSplashScreenBackground(this.$route.params.uid, image).then((res) => {
        const data = res.data;
        this.background = data.uri;
        this.isUploadBackground = false;
        this.$emit("preview-update");
      }).catch(e => {
        this.isUploadBackground = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    uploadLogo(image) {
      if (!image) {
        return;
      }
      this.isUploadedLogo = true;
      adminProjectsService.uploadSplashScreenLogo(this.$route.params.uid, image).then((res) => {
        const data = res.data;
        this.logo = data.uri;
        this.isUploadedLogo = false;
        this.$emit("preview-update");
      }).catch(e => {
        this.isUploadedLogo = false;
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  mounted() {
    this.$emit("preview-update");
  },
  beforeMount() {
    this.getSplashDetail();
  },
  beforeUnmount() {
    this.$emit("preview-update");
  }
};
</script>
