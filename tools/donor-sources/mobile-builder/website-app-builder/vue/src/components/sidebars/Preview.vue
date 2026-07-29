<template>
  <div v-if="!src">
    <ScreenLock is-secondary/>
  </div>
  <template v-else>
    <QueueOverlay v-if="queued" :position="queued" @cancel-session="cancelPreview"/>
    <div class="d-flex justify-center align-center">
      <v-btn-toggle
        density="compact"
        rounded
        class="mt-5 mb-5"
        variant="outlined"
      >
        <v-btn icon="" height="30" width="50" :active="mode === 'android'" @click="mode = 'android'">
          <v-icon icon="mdi-android" color="#00de7a"/>
        </v-btn>
        <v-btn icon="" height="30" width="50" :active="mode === 'ios'" @click="mode = 'ios'">
          <v-icon icon="mdi-apple" size="18" :color="$store.state.theme === 'light' ? 'black' : 'white'"/>
        </v-btn>
      </v-btn-toggle>
    </div>
    <div class="preview-action">
      <v-divider/>
      <div class="d-flex justify-center align-center w-100 h-100 px-3">
        <v-btn variant="text" density="compact" :disabled="!isSessionActive" @click="getRestart">
          <v-icon icon="mdi-refresh" size="16" class="mr-2"/>
          <span class="text-body-2 font-weight-medium">{{ $tr("project", "key_337") }}</span>
        </v-btn>
        <v-btn variant="text" density="compact" :disabled="!isSessionActive" @click="getScreenshot">
          <v-icon icon="mdi-camera-plus-outline" size="16" class="mr-2"/>
          <span class="text-body-2 font-weight-medium">{{ $tr("project", "key_336") }}</span>
        </v-btn>
        <v-btn variant="text" density="compact" :disabled="!isSessionActive" @click="cancelPreview">
          <v-icon icon="mdi-stop-circle-outline" size="16" class="mr-2"/>
          <span class="text-body-2 font-weight-medium">{{ $tr("project", "key_355") }}</span>
        </v-btn>
      </div>
    </div>
    <div class="preview_container">
      <v-btn
        v-if="mode === 'android' && !isSessionActive"
        rounded
        height="61"
        width="216"
        class="android-preview-btn"
        color="primary"
        @click="openPreview"
      >
        {{ $tr("project", "key_317") }}
      </v-btn>
      <v-btn
        v-if="mode === 'ios' && !isSessionActive"
        rounded
        height="61"
        width="216"
        class="ios-preview-btn"
        color="primary"
        @click="openPreview"
      >
        {{ $tr("project", "key_317") }}
      </v-btn>
      <iframe
        v-if="src"
        :key="mode"
        ref="iframe_preview"
        :src="link"
        class="preview_frame"
        frameborder="0"
        scrolling="no"
      ></iframe>
    </div>
    <div style="height: 40px"/>
  </template>
  <v-dialog
    max-width="600"
    scrollable
    v-model="isDialogOpen"
  >
    <PreviewAlert @run-preview="startPreview"/>
  </v-dialog>
</template>

<script>
import { Snack } from 'snack-sdk';
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import projectsService from "@/services/projects/projects.service";
import QueueOverlay from "@/components/blocks/QueueOverlay.vue";
import PreviewAlert from "@/components/modals/PreviewAlert.vue";
export default {
  name: 'Preview',
  components: {PreviewAlert, QueueOverlay, ScreenLock},
  props: {
    seqno: {
      type: Number,
      required: true
    }
  },
  data: () => ({
    mode: "android",
    snack: undefined,
    src: undefined,
    isSessionActive: false,
    queued: 0,
    isDialogOpen: false
  }),
  watch: {
    snack:function (val) {
      if (val) {
        this.initSnack();
      }
    },
    seqno:function (val) {
      this.updatePreview();
    },
    mode:function (val) {
      this.isSessionActive = false;
    },
  },
  computed: {
    device:function () {
      if (this.mode === "ios") {
        return "iphone15pro";
      } else {
        return "pixel4";
      }
    },
    deviceColor: function () {
      return this.$store.state.theme === "light" ? "black" : "white"
    },
    scale: function () {
      return this.mode === "ios" ? 72 : 81
    },
    expUrl: function () {
      return encodeURIComponent(this.src);
    },
    link: function () {
      return this.mode === "ios"
        ? 
        `https://appetize.io/embed/7kfu7r2ugwm7c4risjyigiuhve?autoplay=false&debug=true&device=${this.device}&deviceColor=${this.deviceColor}&embed=true&launchUrl=${this.expUrl}&orientation=portrait&scale=${this.scale}&screenOnly=false&xDocMsg=true&xdocMsg=true&params=%7B%22EXDevMenuDisableAutoLaunch%22%3Atrue%2C%22EXKernelDisableNuxDefaultsKey%22%3Atrue%7D`
        :
        `https://appetize.io/embed/6q5hgbmupyc6c5mhznjd37goga?autoplay=false&debug=true&device=${this.device}&deviceColor=${this.deviceColor}&embed=true&launchUrl=${this.expUrl}&orientation=portrait&scale=${this.scale}&screenOnly=false&xDocMsg=true&xdocMsg=true&params=%7B%22EXDevMenuDisableAutoLaunch%22%3Atrue%2C%22EXKernelDisableNuxDefaultsKey%22%3Atrue%7D`
    }
  },
  methods: {
    buildSnack() {
      let mode = "app";
      if (this.$route.name === "SplashscreenAppSettings") {
        mode = "splash";
      }
      projectsService.launchPreview(this.$route.params.uid, mode).then((res) => {
        const data = res.data;
        let files = data.files;
        files["config.js"] = {
          type: "CODE",
          contents: data.config
        }
        this.snack = new Snack({
          sdkVersion: data.sdkVersion,
          dependencies: data.dependencies,
          files: data.files
        });
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    async initSnack() {
      this.snack.setOnline(true);
      const {url} = await this.snack.getStateAsync();
      this.src = url;
    },
    updatePreview() {
      if (!this.snack) {
        return;
      }
      let mode = "app";
      if (this.$route.name === "SplashscreenAppSettings") {
        mode = "splash";
      }
      projectsService.getPreviewConfig(this.$route.params.uid, mode).then((res) => {
        const data = res.data;
        this.snack.updateFiles({
          'config.js': {
            type: 'CODE',
            contents: data.config,
          },
        });
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    openPreview() {
      if (!this.$store.state.preview_agree) {
        this.isDialogOpen = true;
        return;
      }
      this.startPreview();
    },
    startPreview() {
      this.isDialogOpen = false;
      let iframe = this.$refs.iframe_preview;
      iframe.contentWindow.postMessage('requestSession', '*');
    },
    getScreenshot() {
      let iframe = this.$refs.iframe_preview;
      iframe.contentWindow.postMessage('saveScreenshot', '*');
    },
    getRestart() {
      let iframe = this.$refs.iframe_preview;
      iframe.contentWindow.postMessage('restartApp', '*');
    },
    cancelPreview() {
      let iframe = this.$refs.iframe_preview;
      iframe.contentWindow.postMessage('endSession', '*');
      this.isSessionActive = false;
      this.queued = 0;
    },
    messageEventHandler(event) {
      if (event.data === 'sessionRequested') {
        this.isSessionActive = true;
      } else if (event.data && event.data.type === 'sessionQueuedPosition') {
        // update pool position
        this.queued = event.data.position;
      } else if (event.data && event.data.type === 'accountQueuedPosition') {
        // update pool position
        this.queued = event.data.position;
      } else if (event.data && event.data.type === 'concurrentQueuedPosition') {
        // update pool position
        this.queued = event.data.position;
      } else if(event.data === 'appLaunch'){
        // start app launch
        this.queued = 0;
      } else if(event.data === 'sessionEnded') {
        // session ended
        this.queued = 0;
      }
    }
  },
  beforeMount() {
    this.buildSnack();
  },
  mounted() {
    window.addEventListener('message', this.messageEventHandler, false);
  },
  beforeDestroy() {
    window.removeEventListener('message', this.messageEventHandler, false);
  }
};
</script>
