<template>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_114')"
          variant="outlined"
          density="comfortable"
          :items="backgrounds"
          v-model="mode"
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
        <v-select
          :label="$tr('project', 'key_75')"
          variant="outlined"
          density="comfortable"
          :items="titles"
          v-model="theme"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_82')"
          variant="outlined"
          density="comfortable"
          :items="statusItems"
          v-model="logo_enabled"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_112')"
          variant="outlined"
          density="comfortable"
          v-model="title"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('project', 'key_113')"
          variant="outlined"
          density="comfortable"
          v-model="subtitle"
        ></v-text-field>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <div class="font-weight-medium text-subtitle-2 mb-5">
          {{ $tr('project', 'key_83') }}
        </div>
        <ImageUpload
          class="mb-3"
          :src="background"
          :title="$tr('project', 'key_80')"
          :subtitle="$tr('project', 'key_138')"
          :loading="isUploadBackground"
          @change-image="uploadBackground"
        />
        <ImageUpload
          :src="logo"
          :title="$tr('project', 'key_81')"
          :subtitle="$tr('project', 'key_139')"
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
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateSettings">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import ColorInput from "@/components/forms/ColorInput.vue";
import ImageUpload from "@/components/forms/ImageUpload.vue";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'AdminDrawerSettings',
  components: {FixedFooter, ImageUpload, ColorInput, ScreenLock},
  data: () => ({
    loading: true,
    mode: 0,
    color: "#F44336",
    theme: 0,
    logo_enabled: 0,
    title: "",
    subtitle: "",
    logo: null,
    background: null,
    isUploadBackground: false,
    isUploadedLogo: false,
    isUpdate: false
  }),
  computed: {
    backgrounds: function () {
      return [
        {
          title: this.$tr('project', 'key_115'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_116'),
          value: 1
        },
        {
          title: this.$tr('project', 'key_117'),
          value: 2
        },
      ];
    },
    titles: function () {
      return [
        {
          title: this.$tr('project', 'key_37'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_38'),
          value: 1
        }
      ];
    },
    statusItems: function () {
      return [
        {
          title: this.$tr('project', 'key_44'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_43'),
          value: 1
        }
      ];
    },
  },
  watch: {

  },
  methods: {
    updateSettings() {
      this.isUpdate = true;
      adminProjectsService.updateDrawerSettings(this.$route.params.uid, {
        mode: this.mode,
        color: this.color,
        theme: this.theme,
        logo_enabled: this.logo_enabled,
        title: this.title,
        subtitle: this.subtitle
      }).then(() => {
        this.isUpdate = false;
        this.$refs.footer.showSuccessAlert();
        this.$emit("preview-update");
      }).catch(e => {
        this.isUpdate = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    getDrawerSettings() {
      adminProjectsService.getDrawerSettings(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.mode = data.mode;
        this.color = data.color;
        this.theme = data.theme;
        this.logo_enabled = data.logo_enabled;
        this.title = data.title;
        this.subtitle = data.subtitle;
        this.logo = data.logo;
        this.background = data.background;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    uploadBackground(image) {
      if (!image) {
        return;
      }
      this.isUploadBackground = true;
      adminProjectsService.uploadDrawerBackground(this.$route.params.uid, image).then((res) => {
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
      adminProjectsService.uploadDrawerLogo(this.$route.params.uid, image).then((res) => {
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
  beforeMount() {
    this.getDrawerSettings();
  }
};
</script>
