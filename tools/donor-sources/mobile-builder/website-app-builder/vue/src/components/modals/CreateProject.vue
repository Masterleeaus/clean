<template>
  <v-card>
    <v-toolbar color="transparent">
      <div class="d-flex justify-center align-center align-self-center" style="width: 100vw">
        <v-img :src="$store.state.config.logo" width="34" height="34" :aspect-ratio="1"/>
        <v-btn
          icon="mdi-window-close"
          color="red-accent-3"
          style="position: absolute; right: 18px"
          @click="$store.commit('switchOpenCreateModal')"
        />
      </div>
    </v-toolbar>
    <v-divider/>
    <div class="d-flex justify-center align-center">
      <div class="wizard_container flex-column">
        <div class="text-h5 font-weight-medium mt-8 mb-2">
          {{ $tr('project', 'key_5') }}
        </div>
        <div class="text-body-2 mb-12">
          {{ $tr('project', 'key_6') }}
        </div>
        <div class="w-100">
          <v-text-field
            :label="$tr('project', 'key_7')"
            variant="outlined"
            :autofocus="true"
            density="comfortable"
            v-model="name"
          ></v-text-field>
          <v-text-field
            :label="$tr('project', 'key_8')"
            variant="outlined"
            density="comfortable"
            v-model="link"
          ></v-text-field>
        </div>
        <div class="w-100 mb-5">
          <v-row>
            <v-col cols="12" md="8">
              <div class="text-caption mb-3">
                {{ $tr('project', 'key_9') }}
              </div>
              <ColorSeedPicker :seed="seed" @change="changeSeed"/>
            </v-col>
            <v-col cols="12" md="4">
              <div class="text-caption mb-3">
                {{ $tr('project', 'key_10') }}
              </div>
              <WhiteBlackPicker :is-dark="isDark" @change="changeIsDark"/>
            </v-col>
          </v-row>
        </div>
        <div class="w-100 mb-12">
          <div class="text-caption mb-3">
            {{ $tr('project', 'key_11') }}
          </div>
          <TemplatePicker :template="template" @change="changeTemplate"/>
        </div>
        <div class="w-100 d-flex justify-space-between align-center mb-12">
          <v-btn color="default" variant="flat" @click="$store.commit('switchOpenCreateModal')">
            {{ $tr('project', 'key_145') }}
          </v-btn>
          <v-btn color="primary" variant="flat" :loading="loading" @click="createApp">
            {{ $tr('project', 'key_16') }}
          </v-btn>
        </div>
      </div>
    </div>
  </v-card>
</template>

<script>
import ColorSeedPicker from "@/components/forms/ColorSeedPicker.vue";
import WhiteBlackPicker from "@/components/forms/WheteBlackPicker.vue";
import TemplatePicker from "@/components/forms/TemplatePicker.vue";
import projectsService from "@/services/projects/projects.service";

export default {
  name: 'CreateProject',
  components: {TemplatePicker, WhiteBlackPicker, ColorSeedPicker},
  data: () => ({
    loading: false,
    name: "",
    link: "",
    seed: "#F44336",
    color: "#FFFFFF",
    isDark: true,
    template: 0,
  }),
  watch: {

  },
  methods: {
    changeSeed(val) {
      this.seed = val;
    },
    changeIsDark(val) {
      this.isDark = val;
    },
    changeTemplate(val) {
      this.template = val;
    },
    createApp() {
      this.loading = true;
      projectsService.createProject({
        link: this.link,
        name: this.name,
        template: this.template,
        color: this.seed,
        theme: this.isDark ? 0 : 1
      }).then((res) => {
        const data = res.data;
        this.$router.push({ path: `/private/apps/${data.uid}/main` });
        this.$store.commit('switchOpenCreateModal');
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
