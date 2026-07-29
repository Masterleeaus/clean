<template>
  <PageBar :title="$tr('admin', 'key_148')" />
  <PageLoading v-if="loading" />
  <v-container fluid v-else>
    <v-row>
      <v-col cols="12" class="py-0 mb-5 mt-3">
        <v-card>
          <template v-slot:title>
            <span class="text-h6 text-primary"
              >{{ $tr("admin", "key_149") }} {{ version.version }}</span
            >
            <v-chip
              v-if="statusBadge"
              :color="statusBadge.color"
              class="ml-4"
              size="small"
            >
              {{ statusBadge.title }}
            </v-chip>
          </template>
          <template v-if="isIncomplete" v-slot:append>
            <v-btn
              class="text-none"
              color="primary"
              :text="$tr('admin', 'key_155')"
              variant="text"
              slim
              @click="showUpdateModal = true"
            ></v-btn>
          </template>
          <v-card-text>
            <v-row>
              <v-col cols="12" class="py-2">
                <v-list>
                  <v-list-subheader>{{
                    $tr("admin", "key_150")
                  }}</v-list-subheader>
                  <v-list-item
                    v-for="(feature, index) in features"
                    :key="index"
                  >
                    <template v-slot:prepend>
                      <v-icon color="success" icon="mdi-check-circle"></v-icon>
                    </template>
                    <v-list-item-title>{{ feature }}</v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <InstallUpdates v-model="showUpdateModal" />
</template>

<script>
import { mapGetters } from "vuex";
import { UPDATE_STATUS } from "@/constants/admin";
import PageLoading from "@/components/blocks/PageLoading.vue";
import PageBar from "@/components/blocks/PageBar.vue";
import InstallUpdates from "@/components/modals/InstallUpdates.vue";

export default {
  name: "AdminUpdates",
  components: {
    PageBar,
    PageLoading,
    InstallUpdates,
  },
  data() {
    return {
      loading: false,
      showUpdateModal: false,
    };
  },
  computed: {
    ...mapGetters(["version"]),
    status() {
      if (!this.version) return undefined;

      if (
        !this.version.ui_updates_installed ||
        !this.version.db_updates_installed
      )
        return UPDATE_STATUS.INCOMPLETE;

      if (this.version.updates_available)
        return UPDATE_STATUS.UPDATES_AVAILABLE;

      if (!this.version.repo_updates_installed)
        return UPDATE_STATUS.INCOMPLETE;

      return UPDATE_STATUS.UP_TO_DATE;
    },
    isIncomplete() {
      return this.status === UPDATE_STATUS.INCOMPLETE;
    },
    statusBadge() {
      switch (this.status) {
        case UPDATE_STATUS.INCOMPLETE:
          return { title: this.$tr("admin", "key_153"), color: "error" };
        case UPDATE_STATUS.UPDATES_AVAILABLE:
          return { title: this.$tr("admin", "key_152"), color: "warning" };
        case UPDATE_STATUS.UP_TO_DATE:
          return { title: this.$tr("admin", "key_151"), color: "success" };

        default: return undefined;
      }
    },
    features() {
      return this.version.release_notes || [];
    },
  },
  methods: {
    async fetchVersion() {
      await this.$store.dispatch("fetchVersion");
    },
  },
  beforeMount() {
    this.fetchVersion();
  },
};
</script>
