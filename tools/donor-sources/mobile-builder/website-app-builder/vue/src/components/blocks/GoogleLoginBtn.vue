<template>
  <GoogleLogin
    :client-id="$store.state.config.google.id"
    :callback="onChange"
    popupType="TOKEN"
    class="w-100"
  >
    <v-btn
      size="large"
      flat
      block
      :color="$store.state.theme === 'light' ? 'black' : 'white'"
      :loading="loading"
      class="mt-4 text-none"
    >
      <template v-slot:prepend>
        <img :src="googleLogo" width="28" height="28" alt=""/>
      </template>
      {{ $tr("auth", "key_20") }}
    </v-btn>
  </GoogleLogin>
</template>

<script>
import { GoogleLogin } from 'vue3-google-login';
import gLogo from "@/assets/images/google_login.svg";
export default {
  name: 'GoogleLoginBtn',
  props: {
    onChange: Function,
    loading: Boolean
  },
  components: {
    GoogleLogin
  },
  data: () => ({
    googleLogo: gLogo,
  }),
  watch: {

  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
