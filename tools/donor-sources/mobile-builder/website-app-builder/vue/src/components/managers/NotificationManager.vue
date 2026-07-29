<template>
  <v-snackbar
    v-model="$store.state.snackbar.status"
    :close-on-back="false"
    :close-on-content-click="false"
  >
    {{ message }}

    <template v-slot:actions>
      <v-btn
        color="white"
        variant="text"
        @click="$store.commit('hideSnackbar')"
      >
        OK
      </v-btn>
    </template>
  </v-snackbar>
  <slot></slot>
</template>

<script>
export default {
  name: 'NotificationManager',
  data: () => ({

  }),
  computed: {
    message: function () {
      let val = this.$store.state.snackbar.message;
      if (typeof val === 'string') {
        return val;
      }
      if (val.response) {
        console.log(val.response.data);
        let obj = val.response.data.message;
        if (typeof obj === "object") {
          let text = "";
          Object.keys(obj).forEach(function(key) {
            text += obj[key] + "\n";
          });
          return text;
        }
        return val.response.data.message;
      }
      return "?????"
    }
  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
