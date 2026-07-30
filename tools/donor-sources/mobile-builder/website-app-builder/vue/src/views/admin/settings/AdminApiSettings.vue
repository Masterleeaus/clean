<template>
  <PageBar :title="$tr('admin', 'key_123')"/>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <v-row>
      <v-col md="12" sm="12" cols="12" class="py-0 mb-5 mt-3">
        <v-alert
          type="warning"
          :text="$tr('admin', 'key_132')"
          variant="tonal"
          class="mb-3"
        ></v-alert>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_17')"
          variant="outlined"
          density="comfortable"
          v-model="github_username"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_18')"
          variant="outlined"
          density="comfortable"
          v-model="github_token"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_19')"
          variant="outlined"
          density="comfortable"
          v-model="github_repo"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_130')"
          variant="outlined"
          density="comfortable"
          v-model="github_branch"
        ></v-text-field>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_131')"
          variant="outlined"
          density="comfortable"
          v-model="codemagic_id"
        ></v-text-field>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_20')"
          variant="outlined"
          density="comfortable"
          v-model="codemagic_key"
        ></v-text-field>
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateApiSettings">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import PageLoading from "@/components/blocks/PageLoading.vue";
import PageBar from "@/components/blocks/PageBar.vue";
import adminConfigService from "@/services/config/admin.config.service";
import FixedFooter from "@/components/blocks/FixedFooter.vue";

export default {
  name: 'AdminApiSettings',
  components: {FixedFooter, PageBar, PageLoading},
  data: () => ({
    loading: true,
    codemagic_id: "",
    codemagic_key: "",
    github_branch: "",
    github_token: "",
    github_username: "",
    github_repo: "",
    isUpdate: false
  }),
  methods: {
    updateApiSettings() {
      this.isUpdate = true;
      adminConfigService.updateExternalApiSettings({
        github_username: this.github_username,
        github_token: this.github_token,
        github_repo: this.github_repo,
        codemagic_key: this.codemagic_key,
        codemagic_id: this.codemagic_id,
        github_branch: this.github_branch
      }).then(() => {
        this.$refs.footer.showSuccessAlert();
        this.isUpdate = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    },
    getApiSettings() {
      adminConfigService.getExternalApiSettings().then((res) => {
        const data = res.data;
        this.codemagic_id = data.codemagic_id;
        this.codemagic_key = data.codemagic_key;
        this.github_branch = data.github_branch;
        this.github_token = data.github_token;
        this.github_username = data.github_username;
        this.github_repo = data.github_repo;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      })
    }
  },
  beforeMount() {
    this.getApiSettings();
  }
};
</script>
