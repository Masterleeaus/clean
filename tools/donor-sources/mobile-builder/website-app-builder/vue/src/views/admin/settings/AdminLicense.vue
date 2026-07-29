<template>
  <PageBar :title="$tr('admin', 'key_125')"/>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <v-row>
      <v-col md="12" sm="12" cols="12" class="py-0 mb-5 mt-3">
        <v-alert
          :type="!isActive ? 'error' : 'success'"
          :text="!isActive ? $tr('admin', 'key_140') : $tr('admin', 'key_141')"
          variant="tonal"
          class="mb-3"
        ></v-alert>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_103')"
          variant="outlined"
          density="comfortable"
          v-model="code"
        ></v-text-field>
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" :disabled="isActive" variant="flat" :loading="isUpdate" @click="licenseActivation">
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
  name: 'AdminLicense',
  components: {FixedFooter, PageBar, PageLoading},
  data: () => ({
    loading: true,
    code: "",
    isActive: false,
    isUpdate: false
  }),
  methods: {
    licenseActivation() {
      this.isUpdate = true;
      adminConfigService.activationLicense(this.code).then(() => {
        this.isActive = true;
        this.isUpdate = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    },
    getLicense() {
      adminConfigService.getLicense().then((res) => {
        const data = res.data;
        this.code = data.value;
        if (data.value.length) {
          this.isActive = true;
        }
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      })
    }
  },
  beforeMount() {
    this.getLicense();
  }
};
</script>
