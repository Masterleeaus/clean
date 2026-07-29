<template>
  <PageBar :title="$tr('admin', 'key_127')"/>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_24')"
          variant="outlined"
          density="comfortable"
          v-model="host"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_25')"
          variant="outlined"
          density="comfortable"
          v-model="user"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_26')"
          variant="outlined"
          density="comfortable"
          v-model="port"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_27')"
          variant="outlined"
          density="comfortable"
          v-model="timeout"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_28')"
          variant="outlined"
          density="comfortable"
          v-model="charset"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_29')"
          variant="outlined"
          density="comfortable"
          v-model="sender"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('admin', 'key_30')"
          variant="outlined"
          density="comfortable"
          :type="!isShowPassword ? 'password' : 'text'"
          v-model="password"
        >
          <template v-slot:append-inner>
            <v-btn
              density="comfortable"
              :icon="!isShowPassword ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
              flat
              @click="isShowPassword = !isShowPassword"
            />
          </template>
        </v-text-field>
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateGateway">
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
  name: 'AdminEmailSettings',
  components: {FixedFooter, PageBar, PageLoading},
  data: () => ({
    loading: true,
    isUpdate: false,
    host: "",
    user: "",
    port: "",
    timeout: "",
    charset: "",
    sender: "",
    password: "",
    isShowPassword: false
  }),
  methods: {
    updateGateway() {
      this.isUpdate = true;
      adminConfigService.updateEmailSettings({
        host: this.host,
        user: this.user,
        port: this.port,
        timeout: this.timeout,
        charset: this.charset,
        sender: this.sender,
        password: this.password,
      }).then(() => {
        this.$refs.footer.showSuccessAlert();
        this.isUpdate = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.isUpdate = false;
      });
    },
    getGatewaySettings() {
      adminConfigService.getEmailSettings().then((res) => {
        const data = res.data;
        this.host = data.host;
        this.user = data.user;
        this.port = data.port;
        this.timeout = data.timeout;
        this.charset = data.charset;
        this.sender = data.sender;
        this.password = data.password;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getGatewaySettings();
  }
};
</script>
