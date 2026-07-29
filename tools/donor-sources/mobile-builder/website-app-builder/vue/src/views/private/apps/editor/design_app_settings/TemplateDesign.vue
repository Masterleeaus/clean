<template>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="6" sm="12" cols="12" class="py-0">
        <ColorInput
          :label="$tr('project', 'key_9')"
          v-model="color_theme"
          @change="(val) => this.color_theme = val"
        />
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_10')"
          variant="outlined"
          density="comfortable"
          :items="titles"
          v-model="color_title"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_39')"
          variant="outlined"
          density="comfortable"
          :items="loaders"
          v-model="loader"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_131')"
          variant="outlined"
          density="comfortable"
          :items="statusItems"
          v-model="display_title"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <ColorInput
          :label="$tr('project', 'key_49')"
          v-model="loader_color"
          @change="(val) => this.loader_color = val"
        />
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <v-select
          :label="$tr('project', 'key_42')"
          variant="outlined"
          density="comfortable"
          :items="statusItems"
          v-model="pull_to_refresh"
        ></v-select>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <ColorInput
          :label="$tr('project', 'key_329')"
          v-model="icon_color"
          @change="(val) => this.icon_color = val"
        />
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <ColorInput
          :label="$tr('project', 'key_330')"
          v-model="active_color"
          @change="(val) => this.active_color = val"
        />
      </v-col>
      <v-col md="8" sm="12" cols="12" class="py-0">
        <div class="text-caption mb-3">
          {{ $tr('project', 'key_11') }}
        </div>
        <TemplatePicker :template="template" @change="(val) => template = val"/>
      </v-col>
    </v-row>
    <div style="height: 100px"/>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" :loading="isUpdate" @click="updateSettings">
      {{ $tr('project', 'key_33') }}
    </v-btn>
  </FixedFooter>
</template>

<script>
import projectsService from "@/services/projects/projects.service";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import ColorInput from "@/components/forms/ColorInput.vue";
import TemplatePicker from "@/components/forms/TemplatePicker.vue";
import FixedFooter from "@/components/blocks/FixedFooter.vue";

export default {
  name: 'TemplateDesign',
  components: {FixedFooter, TemplatePicker, ColorInput, ScreenLock},
  data: () => ({
    loading: true,
    color_theme: "#F44336",
    color_title: 0,
    loader: 0,
    pull_to_refresh: 0,
    loader_color: "#F44336",
    template: 0,
    btn_color: "#F44336",
    display_title: 0,
    icon_color: "#F44336",
    active_color: "#F44336",
    isUpdate: false
  }),
  computed: {
    titles: function () {
      return [
        {
          title: this.$tr('project', 'key_37'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_38'),
          value: 1
        }
      ];
    },
    loaders: function () {
      return [
        {
          title: this.$tr('project', 'key_48'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_40'),
          value: 1
        },
        {
          title: this.$tr('project', 'key_41'),
          value: 2
        }
      ];
    },
    statusItems: function () {
      return [
        {
          title: this.$tr('project', 'key_43'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_44'),
          value: 1
        }
      ];
    },
  },
  watch: {

  },
  methods: {
    getDesign() {
      this.loading = true;
      projectsService.getDesignSettings(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.color_theme = data.color_theme;
        this.color_title = data.color_title;
        this.loader = data.loader;
        this.pull_to_refresh = data.pull_to_refresh;
        this.loader_color = data.loader_color;
        this.template = data.template;
        this.btn_color = data.btn_color;
        this.display_title = data.display_title;
        this.icon_color = data.icon_color;
        this.active_color = data.active_color;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    updateSettings() {
      this.isUpdate = true;
      projectsService.updateTemplateSettings(this.$route.params.uid, {
        color_theme: this.color_theme,
        color_title: this.color_title,
        template: this.template,
        loader: this.loader,
        pull_to_refresh: this.pull_to_refresh,
        loader_color: this.loader_color,
        display_title: this.display_title,
        icon_color: this.icon_color,
        active_color: this.active_color
      }).then(() => {
        this.isUpdate = false;
        this.$refs.footer.showSuccessAlert();
        this.$emit("preview-update");
      }).catch(e => {
        this.isUpdate = false;
        this.$store.commit('openSnackbar', e);
      })
    }
  },
  beforeMount() {
    this.getDesign();
  }
};
</script>
