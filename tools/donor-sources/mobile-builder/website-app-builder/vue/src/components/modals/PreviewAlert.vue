<template>
  <v-card>
    <v-container fluid>
      <div class="text-h5 text-center font-weight-medium mb-5">
        {{ $tr('project', 'key_326') }}
      </div>
      <v-alert
        type="info"
        variant="tonal"
        class="mb-3 mt-3"
      >
        {{ $tr('project', 'key_356') }}
      </v-alert>
      <v-row class="mt-5 mb-2">
        <v-col md="4" sm="12" cols="12" class="text-center">
          <RtcBigIcon class="mb-3 text-primary"/>
          <div class="text-body-1 font-weight-bold mb-3">
            {{ $tr('project', 'key_318') }}
          </div>
          <div class="text-body-2">
            {{ $tr('project', 'key_319') }}
          </div>
        </v-col>
        <v-col md="4" sm="12" cols="12" class="text-center">
          <FlipBigIcon class="mb-3 text-primary"/>
          <div class="body-1 font-weight-bold black--text mb-3">
            {{ $tr('project', 'key_320') }}
          </div>
          <div class="text-body-2">
            {{ $tr('project', 'key_321') }}
          </div>
        </v-col>
        <v-col md="4" sm="12" cols="12" class="text-center">
          <AlertDeviceBigIcon class="mb-3 text-primary"/>
          <div class="body-1 font-weight-bold black--text mb-3">
            {{ $tr('project', 'key_322') }}
          </div>
          <div class="text-body-2">
            {{ $tr('project', 'key_323') }}
          </div>
        </v-col>
      </v-row>
    </v-container>
    <v-divider/>
    <v-container fluid>
      <div class="d-flex justify-space-between align-center">
        <v-checkbox
          color="primary"
          v-model="showMode"
          hide-details
          :label="$tr('project', 'key_325') "
        ></v-checkbox>
        <v-btn variant="flat" color="primary" @click="closeModal">
          {{ $tr('project', 'key_324') }}
        </v-btn>
      </div>
    </v-container>
  </v-card>
</template>

<script>
import RtcBigIcon from "@/components/icons/RtcBigIcon.vue";
import FlipBigIcon from "@/components/icons/FlipBigIcon.vue";
import AlertDeviceBigIcon from "@/components/icons/AlertDeviceBigIcon.vue";

export default {
  name: 'PreviewAlert',
  components: {AlertDeviceBigIcon, FlipBigIcon, RtcBigIcon},
  data: () => ({
    showMode: true
  }),
  watch: {

  },
  methods: {
    closeModal() {
      if (this.showMode) {
        this.$store.commit('setPreviewAgree', true);
      }
      this.$emit("run-preview");
    }
  },
  beforeMount() {

  }
};
</script>
