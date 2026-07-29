<template>
  <div  v-on:keyup.enter="signUp">
    <div class="text-center text-h5 font-weight-medium mb-10">
      {{ $tr("auth", "key_3") }}
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
    <v-text-field
      :label="$tr('auth', 'key_10')"
      variant="outlined"
      :type="!showRePassword ? 'password' : 'text'"
      density="comfortable"
      v-model="rePassword"
    >
      <template v-slot:append-inner>
        <v-btn
          density="comfortable"
          :icon="!showRePassword ? 'mdi-eye-outline' : 'mdi-eye-off-outline'"
          flat
          @click="showRePassword = !showRePassword"
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
      @click="signUp"
    >
      {{ $tr("auth", "key_15") }}
    </v-btn>
    <div class="text-body-2 text-center text-blue-grey-lighten-2 mt-7 mb-7">
      {{ $tr("auth", "key_11") }}
    </div>
    <div class="text-center">
      <router-link to="/" class="text-primary text-decoration-none">
        {{ $tr("auth", "key_16") }}
      </router-link>
    </div>
  </div>
</template>

<script>
import authService from "@/services/auth/auth.service";

export default {
  name: 'Registration',
  data: () => ({
    email: "",
    password: "",
    rePassword: "",
    showPassword: false,
    showRePassword: false,
    loading: false
  }),
  watch: {

  },
  methods: {
    signUp() {
      this.loading = true;
      authService.createAccount(this.email, this.password, this.rePassword).then((res) => {
        const data = res.data;
        this.$store.commit('setUser', data);
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.loading = false;
      });
    }
  },
  beforeMount() {

  }
};
</script>
