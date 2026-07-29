<template>
  <div v-on:keyup.enter="signIn">
    <div class="text-center text-h5 font-weight-medium mb-10">
      {{ $tr("auth", "key_1") }}
    </div>
    <v-text-field
      :label="$tr('auth', 'key_5')"
      variant="outlined"
      :autofocus="true"
      type="email"
      density="comfortable"
      v-model="email"
    ></v-text-field>
    <v-text-field
      :label="$tr('auth', 'key_6')"
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
    <v-btn
      size="large"
      flat
      block
      color="primary"
      :loading="loading"
      class="text-none"
      @click="signIn"
    >
      {{ $tr("auth", "key_8") }}
    </v-btn>
    <GoogleLoginBtn
      v-if="$store.state.config.google.enabled"
      :loading="gLoading"
      :on-change="googleLogin"
    />
    <div class="text-center mt-7 mb-2">
      <router-link to="/auth/forgot" class="text-primary text-decoration-none text-body-1">
        {{ $tr("auth", "key_9") }}
      </router-link>
    </div>
    <div class="text-center">
      {{ $tr("auth", "key_21") }}
      <router-link to="/auth/sign_up" class="text-primary text-decoration-none text-body-1">
        {{ $tr("auth", "key_3") }}
      </router-link>
    </div>
  </div>
</template>

<script>
import authService from "@/services/auth/auth.service";
import GoogleLoginBtn from "@/components/blocks/GoogleLoginBtn.vue";
export default {
  name: 'Login',
  components: {GoogleLoginBtn},
  data: () => ({
    email: "",
    password: "",
    showPassword: false,
    loading: false,
    gLoading: false
  }),
  methods: {
    googleLogin(object) {
      this.gLoading = true;
      authService.loginWithGoogle(object.access_token).then((res) => {
        const data = res.data;
        this.$store.commit('setUser', data);
        this.gLoading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.gLoading = false;
      });
    },
    signIn() {
      this.loading = true;
      authService.loginWithEmail(this.email, this.password).then((res) => {
        const data = res.data;
        this.$store.commit('setUser', data);
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.loading = false;
      });
    }
  }
};
</script>
