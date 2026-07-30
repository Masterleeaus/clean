<template>
  <v-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    max-width="600"
  >
    <v-card>
      <v-card-title class="text-h6">
        {{ $tr("admin", "key_155") }}
      </v-card-title>
      <v-card-text>
        <v-list lines="two">
          <v-list-item>
            <template v-slot:prepend>
              <v-icon color="success" icon="mdi-check-circle"></v-icon>
            </template>
            <v-list-item-title>{{ $tr("admin", "key_156") }}</v-list-item-title>
          </v-list-item>

          <v-list-item>
            <template v-slot:prepend>
              <v-icon
                :color="version.db_updates_installed ? 'success' : 'error'"
                :icon="
                  version.db_updates_installed
                    ? 'mdi-check-circle'
                    : 'mdi-alert-circle'
                "
              ></v-icon>
            </template>
            <v-list-item-title>{{ $tr("admin", "key_157") }}</v-list-item-title>
            <v-list-item-subtitle
              v-if="!version.db_updates_installed"
              class="text-error"
            >
              {{ $tr("admin", "key_160") }}
            </v-list-item-subtitle>
          </v-list-item>

          <v-list-item>
            <template v-slot:prepend>
              <v-icon
                :color="version.ui_updates_installed ? 'success' : 'error'"
                :icon="
                  version.ui_updates_installed
                    ? 'mdi-check-circle'
                    : 'mdi-alert-circle'
                "
              ></v-icon>
            </template>
            <v-list-item-title>{{ $tr("admin", "key_158") }}</v-list-item-title>
            <v-list-item-subtitle
              v-if="!version.ui_updates_installed"
              class="text-error"
            >
              {{ $tr("admin", "key_160") }}
            </v-list-item-subtitle>
          </v-list-item>

          <v-list-item>
            <template v-slot:prepend>
              <v-icon
                :color="repoUpdatesColor"
                :icon="repoUpdatesIcon"
              ></v-icon>
            </template>
            <v-list-item-title>{{ $tr("admin", "key_159") }}</v-list-item-title>
            <v-list-item-subtitle :class="`text-${repoUpdatesColor} block`">
              {{ repoUpdatesSubtitle }}
            </v-list-item-subtitle>

            <template
              v-if="
                !version.repo_updates_installed &&
                !repo_updates_installing_result
              "
              v-slot:append
            >
              <v-btn
                color="secondary"
                size="small"
                variant="text"
                type="button"
                :loading="repo_updates_installing"
                :disabled="repo_updates_installing"
                @click="handleInstall"
                >Install</v-btn
              >
            </template>
          </v-list-item>
        </v-list>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          variant="text"
          @click="$emit('update:modelValue', false)"
          >Close</v-btn
        >
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapGetters } from "vuex";
import AdminUpdatesService from "@/services/updates/admin.updates.service";

export default {
  name: "InstallUpdates",
  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      repo_updates_installing: false,
      repo_updates_installing_result: null,
    };
  },
  computed: {
    ...mapGetters(["version"]),
    repoUpdatesColor() {
      if (this.version.repo_updates_installed) return "success";
      if (
        !this.version.repo_updates_installed &&
        !this.repo_updates_installing &&
        !this.repo_updates_installing_result
      )
        return "warning";
      if (this.repo_updates_installing) return "info";
      if (this.repo_updates_installing_result?.status === "success") return "success";
      return "error";
    },
    repoUpdatesIcon() {
      if (this.version.repo_updates_installed) return "mdi-check-circle";
      if (this.repo_updates_installing) return "mdi-progress-clock";
      if (this.repo_updates_installing_result?.status === "success")
        return "mdi-check-circle";
      return "mdi-alert-circle";
    },
    repoUpdatesSubtitle() {
      if (this.version.repo_updates_installed && !this.repo_updates_installing_result) return "";
      if (
        !this.version.repo_updates_installed &&
        !this.repo_updates_installing &&
        !this.repo_updates_installing_result
      )
        return this.$tr("admin", "key_161");
      if (this.repo_updates_installing) return this.$tr("admin", "key_162");
      if (this.repo_updates_installing_result)
        return this.repo_updates_installing_result.message;

      return this.$tr("admin", "key_162");
    },
  },
  methods: {
    async fetchVersion() {
      await this.$store.dispatch("fetchVersion");
    },
    async handleInstall() {
      try {
        this.repo_updates_installing = true;
        const { data } = await AdminUpdatesService.installRepositoryUpdates();

        if (!data.event)
          throw new Error(data.message || this.$tr("admin", "key_164"));

        const interval = setInterval(async () => {
          const { data } =
            await AdminUpdatesService.getRepositoryUpdateStatus();

          if (data.event && data.status === "completed") {
            clearInterval(interval);

            if (data.conclusion !== "success")
              throw new Error(this.$tr("admin", "key_164"));

            this.fetchVersion();
            this.repo_updates_installing = false;
            this.repo_updates_installing_result = {
              status: "success",
              message: this.$tr("admin", "key_163"),
            };
          }
        }, 5000);
      } catch (error) {
        this.repo_updates_installing = false;
        this.repo_updates_installing_result = {
          status: "error",
          message: error.message,
        };
      }
    },
  },
};
</script>
