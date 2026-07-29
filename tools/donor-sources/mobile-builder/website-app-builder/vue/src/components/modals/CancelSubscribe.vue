<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title class="text-red-accent-3">
        {{ $tr('project', 'key_142') }}
      </v-toolbar-title>
      <v-spacer/>
      <v-btn
        icon="mdi-window-close"
        color="red-accent-3"
        density="default"
        @click="this.$emit('close')"
      />
    </v-toolbar>
    <v-divider/>
    <v-container fluid>
      <v-row>
        <v-col md="12" sm="12" cols="12">
          {{ $tr('project', 'key_385') }}
        </v-col>
      </v-row>
    </v-container>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn flat color="red-accent-3" :loading="loading" @click="getCancelMethod">
        {{ $tr('project', 'key_383') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import subscribeService from "@/services/subscribe/subscribe.service";

export default {
  name: 'CancelSubscribe',
  props: {
    uid: {
      type: String,
      required: true
    }
  },
  data: () => ({
    loading: false
  }),
  watch: {

  },
  methods: {
    getCancelMethod() {
      this.loading = true;
      subscribeService.getCancelRoute().then((res) => {
        const data = res.data;
        this.cancelSubscribe(data.url);
      }).catch(e => {
        this.loading = false;
        this.$store.commit('openSnackbar', e);
      })
    },
    cancelSubscribe(url) {
      const link = `${url}?uid=${this.uid}`
      subscribeService.cancelSubscribe(link).then(() => {
        this.$emit("success-cancel");
      }).catch(e => {
        this.loading = false;
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {

  }
};
</script>
