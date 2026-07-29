<template>
  <PageBar :title="$tr('admin', 'key_124')"/>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <v-row>
      <v-col md="12" sm="12" cols="12" class="py-0 mb-5 mt-3">
        <v-alert
          type="warning"
          :text="$tr('admin', 'key_133')"
          variant="tonal"
          class="mb-3"
        ></v-alert>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_134')"
          variant="outlined"
          density="comfortable"
          v-model="one_signal_auth_key"
        ></v-text-field>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_135')"
          variant="outlined"
          density="comfortable"
          v-model="one_signal_organization_id"
        ></v-text-field>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <div :class="`body-2 mb-3 ${!one_signal_fcm_file.length ? 'text-red-accent-3' : 'text-success'}`">
          {{$tr('admin', 'key_136')}} {{!one_signal_fcm_file.length ? $tr('admin', 'key_137') : $tr('admin', 'key_138')}}
        </div>
        <ImageUpload
          class="mb-3"
          :src="null"
          :title="$tr('admin', 'key_136')"
          :subtitle="$tr('admin', 'key_139')"
          :loading="isUploadJson"
          accept=".json"
          @change-image="uploadJson"
        />
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updatePushSettings">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import PageLoading from "@/components/blocks/PageLoading.vue";
import PageBar from "@/components/blocks/PageBar.vue";
import adminConfigService from "@/services/config/admin.config.service";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import ImageUpload from "@/components/forms/ImageUpload.vue";

export default {
  name: 'AdminPushSettings',
  components: {ImageUpload, FixedFooter, PageBar, PageLoading},
  data: () => ({
    loading: true,
    one_signal_auth_key: "",
    one_signal_fcm_file: "",
    one_signal_organization_id: "",
    isUpdate: false,
    isUploadJson: false
  }),
  methods: {
    uploadJson(file) {
      if (!file) {
        return;
      }
      this.isUploadJson = true;
      adminConfigService.uploadFcm(file).then((res) => {
        const data = res.data;
        this.one_signal_fcm_file = data.one_signal_fcm_file;
        this.isUploadJson = false;
      }).catch(e => {
        this.isUploadJson = false;
        this.$store.commit('openSnackbar', e);
      });
    },
    updatePushSettings() {
      this.isUpdate = true;
      adminConfigService.updateOnesignalSettings({
        one_signal_auth_key: this.one_signal_auth_key,
        one_signal_organization_id: this.one_signal_organization_id
      }).then(() => {
        this.$refs.footer.showSuccessAlert();
        this.isUpdate = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    },
    getPushSettings() {
      adminConfigService.getOnesignalSettings().then((res) => {
        const data = res.data;
        this.one_signal_auth_key = data.one_signal_auth_key;
        this.one_signal_fcm_file = data.one_signal_fcm_file;
        this.one_signal_organization_id = data.one_signal_organization_id;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getPushSettings();
  }
};
</script>
