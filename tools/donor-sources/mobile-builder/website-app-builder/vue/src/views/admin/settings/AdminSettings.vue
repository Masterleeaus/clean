<template>
  <PageBar :title="$tr('admin', 'key_122')"/>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_14')"
          variant="outlined"
          density="comfortable"
          v-model="site_name"
        ></v-text-field>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_15')"
          variant="outlined"
          density="comfortable"
          v-model="site_url"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_22')"
          variant="outlined"
          density="comfortable"
          v-model="currency_code"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_23')"
          variant="outlined"
          density="comfortable"
          v-model="currency_symbol"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_37')"
          variant="outlined"
          density="comfortable"
          v-model="google_id"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('admin', 'key_38')"
          variant="outlined"
          density="comfortable"
          :items="statusItems"
          v-model="google_enabled"
        ></v-select>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0 mt-5">
        <ImageUpload
          class="mb-3"
          :src="site_logo"
          :title="$tr('admin', 'key_129')"
          :subtitle="$tr('admin', 'key_128')"
          :loading="isUploadLogo"
          @change-image="uploadLogo"
        />
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateSiteSettings">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import adminConfigService from "@/services/config/admin.config.service";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import ImageUpload from "@/components/forms/ImageUpload.vue";

export default {
  name: 'AdminSettings',
  components: {ImageUpload, FixedFooter, PageLoading, PageBar},
  data: () => ({
    loading: true,
    site_name: "",
    site_url: "",
    site_logo: "",
    google_enabled: 0,
    google_id: "",
    currency_symbol: "",
    currency_code: "",
    isUpdate: false,
    isUploadLogo: false
  }),
  computed: {
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
  methods: {
    uploadLogo(image) {
      if (!image) {
        return;
      }
      this.isUploadLogo = true;
      adminConfigService.uploadWebsiteLogo(image).then((res) => {
        const data = res.data;
        this.site_logo = data.logo;
        this.$store.commit('setLogo', data.logo);
        this.isUploadLogo = false;
      }).catch(e => {
        this.isUploadLogo = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    updateSiteSettings() {
      this.isUpdate = true;
      adminConfigService.updateWebsiteSettings({
        site_name: this.site_name,
        site_url: this.site_url,
        currency_code: this.currency_code,
        currency_symbol: this.currency_symbol,
        google_id: this.google_id,
        google_enabled: this.google_enabled
      }).then(() => {
        this.$refs.footer.showSuccessAlert();
        this.isUpdate = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    },
    getSiteSettings() {
      adminConfigService.getWebsiteSettings().then((res) => {
        const data = res.data;
        this.site_name = data.site_name;
        this.site_url = data.site_url;
        this.site_logo = data.site_logo;
        this.google_id = data.google_id;
        this.google_enabled = data.google_enabled;
        this.currency_symbol = data.currency_symbol;
        this.currency_code = data.currency_code;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getSiteSettings();
  }
};
</script>
