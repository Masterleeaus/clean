<template>
  <ScreenLock v-if="loading"/>
  <template v-else>
    <div v-if="error.length">
      <div style="height: 100vh">
        <div class="h-100 d-flex flex-column justify-center align-center align-self-center ga-4">
          <div class="text-blue-grey-lighten-2">
            <AlertDeviceBigIcon size="56"/>
          </div>
          <div class="text-body-1">
            {{ error }}
          </div>
        </div>
      </div>
    </div>
    <slot v-else></slot>
  </template>
</template>

<script>
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import configService from "@/services/config/config.service";
import AlertDeviceBigIcon from "@/components/icons/AlertDeviceBigIcon.vue";

export default {
  name: 'ConfigManager',
  components: {AlertDeviceBigIcon, ScreenLock},
  data: () => ({
    loading: true,
    error: ""
  }),
  watch: {
    '$store.state.api_url': function() {
      this.initGlobalConfig();
    },
  },
  methods: {
    initSystemConfig() {
      configService.getLocaleAppConfig().then((res) => {
        const data = res.data;
        this.$store.commit('setApiUrl', data.api_url);
        this.$store.commit('setOffset', data.offset);
      }).catch(e => {
        this.error = e.message;
        this.loading = false;
      });
      configService.getVersionConfig().then((res) => {
        const data = res.data;
        this.$store.commit('setUiCompatibilityDate', data.compatibility_date);
      }).catch(e => {
        this.error = e.message;
        this.loading = false;
      });
    },
    initGlobalConfig() {
      configService.getGlobalConfig().then((res) => {
        const data = res.data;
        this.$store.commit('setLanguage', data.language);
        this.$store.commit('setConfig', data.configs);
        this.$store.commit('setLanguageHeader', data.locale);
        this.$store.commit('setVersion', data.version);
        this.loading = false;
      }).catch(e => {
        this.error = e.message;
        this.loading = false;
      });
    },
  },
  beforeMount() {
    this.$store.commit('initialiseVars');
    this.initSystemConfig();
  }
};
</script>
