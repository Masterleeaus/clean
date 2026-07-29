<template>
<div>
  <div class="text-center text-h5 font-weight-medium mb-10">
    {{ $tr("auth", "key_18") }}
  </div>
  <div class="d-flex justify-center">
    <v-progress-circular
      indeterminate
      color="primary"
      width="2"
    ></v-progress-circular>
  </div>
</div>
</template>

<script>
import authService from "@/services/auth/auth.service";

export default {
  name: 'Reset',
  data: () => ({

  }),
  watch: {

  },
  methods: {
    resetPass() {
      authService.resetPassword(this.$route.query.token).then((res) => {
        const data = res.data;
        this.$store.commit('openSnackbar', data.message);
        this.$router.push({ path: '/', replace: true });
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.$router.push({ path: '/', replace: true });
      });
    }
  },
  beforeMount() {
    this.resetPass();
  }
};
</script>
