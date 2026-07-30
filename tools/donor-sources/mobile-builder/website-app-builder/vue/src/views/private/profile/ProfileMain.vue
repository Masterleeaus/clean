<template>
  <PageBar :title="$tr('menu', 'key_24')"/>
  <v-container fluid>
    <v-row>
      <v-col md="12" sm="12" cols="12" class="py-0">
        <v-alert
          type="info"
          :text="$tr('project', 'key_387')"
          variant="tonal"
          class="mb-7 mt-3"
        ></v-alert>
        <v-text-field
          :label="$tr('project', 'key_7')"
          variant="outlined"
          density="comfortable"
          readonly
          v-model="email"
        ></v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('auth', 'key_23')"
          variant="outlined"
          :type="!showPassword ? 'password' : 'text'"
          density="comfortable"
          v-model="password"
        >
          <template v-slot:append-inner>
            <v-btn
              density="comfortable"
              :icon="!showPassword ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
              flat
              @click="showPassword = !showPassword"
            />
          </template>
        </v-text-field>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-text-field
          :label="$tr('auth', 'key_19')"
          variant="outlined"
          :type="!showNewPassword ? 'password' : 'text'"
          density="comfortable"
          v-model="newPassword"
        >
          <template v-slot:append-inner>
            <v-btn
              density="comfortable"
              :icon="!showNewPassword ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
              flat
              @click="showNewPassword = !showNewPassword"
            />
          </template>
        </v-text-field>
      </v-col>
    </v-row>
  </v-container>
  <FixedFooter ref="footer" :right="0">
    <v-btn color="primary" variant="flat" :loading="loading" @click="startUpdatePassword">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import profileService from "@/services/profile/profile.service";

export default {
  name: 'ProfileMain',
  components: {FixedFooter, PageBar},
  data: () => ({
    email: "",
    password: "",
    showPassword: false,
    newPassword: "",
    showNewPassword: false,
    loading: false
  }),
  watch: {

  },
  methods: {
    startUpdatePassword() {
      this.loading = true;
      profileService.updatePassword(this.password, this.newPassword).then(() => {
        this.$refs.footer.showSuccessAlert();
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.loading = false;
      });
    }
  },
  beforeMount() {
    this.email = this.$store.state.user.email;
  }
};
</script>
