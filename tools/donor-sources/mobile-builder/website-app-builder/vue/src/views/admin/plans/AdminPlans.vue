<template>
  <PageBar :title="$tr('admin', 'key_9')" />
  <PageLoading v-if="loading" />
  <template v-else>
    <Placeholder v-if="!list.length">
      <template v-slot:icon>
        <TagIcon :size="56" />
      </template>
      <template v-slot:title>
        {{ $tr("admin", "key_44") }}
      </template>
      <template v-slot:subtitle>
        {{ $tr("admin", "key_45") }}
      </template>
      <template v-slot:action>
        <v-btn variant="tonal" color="primary" @click="openCreate">
          {{ $tr("admin", "key_42") }}
        </v-btn>
      </template>
    </Placeholder>
    <v-container fluid v-else>
      <v-row class="mt-3">
        <v-col md="12" sm="12" cols="12" class="py-0">
          <div class="w-100 d-flex justify-space-between align-center mb-3">
            <div class="font-weight-medium text-subtitle-2">
              {{ $tr("admin", "key_43") }}
            </div>
            <v-btn variant="flat" color="primary" @click="openCreate">
              {{ $tr("admin", "key_42") }}
            </v-btn>
          </div>
          <v-table>
            <thead>
              <tr>
                <th class="text-left" width="250px">
                  {{ $tr("admin", "key_165") }}
                </th>
                <th class="text-center" width="150px">
                  {{ $tr("admin", "key_39") }}
                </th>
                <th class="text-center" width="150px">
                  {{ $tr("admin", "key_40") }}
                </th>
                <th class="text-center" width="150px">
                  {{ $tr("admin", "key_41") }}
                </th>
                <th class="text-center" width="150px">
                  {{ $tr("admin", "key_144") }}
                </th>
                <th class="text-right">
                  {{ $tr("admin", "key_46") }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(plan, index) in list">
                <td class="text-left">{{ typeText(plan.type) }}</td>
                <td class="text-center">
                  {{ countText(plan.count) }}
                </td>
                <td class="text-center">
                  {{ plan.price }} {{ $store.state.config.currency }}
                </td>
                <td class="text-center">
                  {{ plan.save }} {{ $store.state.config.currency }}
                </td>
                <td class="text-center">
                  {{ countText(plan.build_count) }}
                </td>
                <td class="text-right">
                  <div>
                    <v-btn
                      variant="text"
                      icon=""
                      density="compact"
                      color="primary"
                      class="mr-3"
                      @click="openUpdate(index)"
                    >
                      <EditMiniIcon :size="16" />
                    </v-btn>
                    <v-btn
                      variant="text"
                      icon=""
                      density="compact"
                      color="red-accent-3"
                      :loading="index === loadingIndex"
                      @click="openDelete(index)"
                    >
                      <DeleteMiniIcon :size="16" />
                    </v-btn>
                  </div>
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-col>
      </v-row>
    </v-container>
  </template>
  <v-dialog
    scrollable
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="modal.status"
  >
    <ChangePlan
      :id="modal.id"
      :price="modal.price"
      :save="modal.save"
      :count="modal.count"
      :build_count="modal.build_count"
      :api_id="modal.api_id"
      :type="modal.type"
      @close="modal.status = false"
      @on-success="changeActive"
    />
  </v-dialog>
  <v-dialog
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="modalDelete.status"
  >
    <v-card>
      <v-toolbar color="transparent">
        <v-toolbar-title class="text-red-accent-3">
          {{ $tr("admin", "key_173") }}
        </v-toolbar-title>
        <v-spacer />
        <v-btn
          icon="mdi-window-close"
          color="red-accent-3"
          density="default"
          @click="closeDelete()"
        />
      </v-toolbar>
      <v-divider />
      <v-container fluid>
        <v-row>
          <v-col md="12" sm="12" cols="12">
            {{ $tr("admin", "key_174") }}
          </v-col>
        </v-row>
      </v-container>
      <v-container fluid class="d-flex justify-end align-center">
        <v-btn
          flat
          color="red-accent-3"
          :loading="modalDelete.loading"
          @click="removePlan(modalDelete.index)"
        >
          {{ $tr("admin", "key_173") }}
        </v-btn>
      </v-container>
    </v-card>
  </v-dialog>
</template>

<script>
import PageLoading from "@/components/blocks/PageLoading.vue";
import PageBar from "@/components/blocks/PageBar.vue";
import plansService from "@/services/plans/plans.service";
import Placeholder from "@/components/blocks/Placeholder.vue";
import TagIcon from "@/components/icons/TagIcon.vue";
import EditMiniIcon from "@/components/icons/EditMiniIcon.vue";
import DeleteMiniIcon from "@/components/icons/DeleteMiniIcon.vue";
import ChangePlan from "@/components/modals/ChangePlan.vue";

export default {
  name: "AdminPlans",
  components: {
    ChangePlan,
    DeleteMiniIcon,
    EditMiniIcon,
    TagIcon,
    Placeholder,
    PageBar,
    PageLoading,
  },
  data: () => ({
    loading: true,
    list: [],
    modal: {
      index: undefined,
      status: false,
      id: null,
      price: "",
      save: "",
      count: 1,
      build_count: 0,
      api_id: "",
      type: "subscription",
    },
    modalDelete: {
      index: undefined,
      status: false,
    },
    loadingIndex: undefined,
  }),
  methods: {
    typeText(type) {
      switch (type) {
        case "subscription":
          return this.$tr("admin", "key_168");
        case "per_build":
          return this.$tr("admin", "key_169");
        default:
          return this.$tr("admin", "key_168");
      }
    },
    countText(count) {
      return count === 0 ? this.$tr("admin", "key_170") : count;
    },
    changeActive() {
      this.modal.status = false;
      this.getPlans();
    },
    openCreate() {
      this.modal = {
        index: undefined,
        status: true,
        id: null,
        price: "",
        save: "",
        count: 1,
        build_count: 0,
        api_id: "",
        type: "subscription",
      };
    },
    openUpdate(index) {
      const item = this.list[index];
      this.modal = {
        index: index,
        status: true,
        id: item.id,
        price: item.price,
        save: item.save,
        count: item.count,
        build_count: item.build_count,
        api_id: item.api_id,
        type: item.type,
      };
    },
    getPlans() {
      plansService
        .getPlans()
        .then((res) => {
          const data = res.data;
          this.list = data.list;
          this.loading = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
        });
    },
    openDelete(index) {
      this.modalDelete.index = index;
      this.modalDelete.status = true;
      this.modalDelete.loading = false;
    },
    closeDelete() {
      this.modalDelete.status = false;
      this.modalDelete.index = undefined;
      this.modalDelete.loading = false;
    },
    removePlan(index) {
      this.modalDelete.loading = true;
      this.loadingIndex = index;
      const item = this.list[index];
      plansService
        .removePlan(item.id)
        .then(() => {
          this.loadingIndex = undefined;
          this.list.splice(index, 1);
          this.closeDelete();
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
          this.loadingIndex = undefined;
        });
    },
  },
  beforeMount() {
    this.getPlans();
  },
};
</script>
