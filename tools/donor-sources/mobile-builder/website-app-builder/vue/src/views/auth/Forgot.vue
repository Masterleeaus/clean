<template>
  <div v-on:keyup.enter="createRequest">
    <div class="text-center text-h5 font-weight-medium mb-10">
      {{ $tr("auth", "key_4") }}
    </div>
    <v-text-field
      :label="$tr('auth', 'key_5')"
      variant="outlined"
      :autofocus="true"
      type="email"
      density="comfortable"
      v-model="email"
    ></v-text-field>
    <v-btn
      size="large"
      flat
      block
      color="primary"
      :loading="loading"
      class="text-none"
      @click="createRequest"
    >
      {{ $tr("auth", "key_22") }}
    </v-btn>
    <div class="text-center mt-7 mb-2">
      <router-link to="/" class="text-primary text-decoration-none text-body-1">
        {{ $tr("auth", "key_16") }}
      </router-link>
    </div>
  </div>
</template>

<script>
import authService from "@/services/auth/auth.service";

export default {
  name: 'Forgot',
  data: () => ({
    email: "",
    loading: false
  }),
  watch: {

  },
  methods: {
    createRequest() {
      this.loading = true;
      authService.createForgotRequest(this.email).then((res) => {
        const data = res.data;
        this.$store.commit('openSnackbar', data.message);
        this.loading = false;
        this.$router.push({ path: '/', replace: true });
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
