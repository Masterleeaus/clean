<template>
  <v-app>
    <img
      :src="$store.state.config.logo"
      class="logo_auth"
      alt="Logo"
    />
    <v-main class="d-flex justify-center align-center align-self-center">
      <div style="width: 360px">
        <router-view/>
      </div>
    </v-main>
  </v-app>
</template>

<script>
export default {
  name: 'AuthLayout',
  data: () => ({

  }),
  watch: {
    '$store.state.user.token': function() {
      this.checkAccess();
    }
  },
  methods: {
    checkAccess() {
      if (this.$store.state.user.token.access.length) {
        this.$router.push({ path: '/private/apps', replace: true });
      }
    }
  },
  beforeMount() {
    this.checkAccess();
  }
};
</script>
