<template>
  <PageBar :title="$tr('admin', 'key_120')" />
  <PageLoading v-if="loading" />
  <v-container fluid v-else>
    <v-col md="12" sm="12" cols="12" class="py-0 mb-5 mt-3">
      <v-alert
        type="warning"
        :text="$tr('admin', 'key_175')"
        variant="tonal"
        class="mb-3"
      ></v-alert>
    </v-col>
    <v-list item-props density="comfortable">
      <div v-for="(method, index) in list">
        <v-list-item lines="two" density="default" @click="openModal(index)">
          <template v-slot:prepend>
            <v-avatar size="38" :image="method.logo" />
          </template>
          <v-list-item-title class="font-weight-medium">{{
            method.name
          }}</v-list-item-title>
          <template v-slot:append>
            <v-list-item-action>
              <v-chip
                variant="tonal"
                density="compact"
                :color="!method.status ? 'default' : 'success'"
              >
                {{
                  !method.status
                    ? $tr("project", "key_44")
                    : $tr("project", "key_43")
                }}
              </v-chip>
            </v-list-item-action>
          </template>
        </v-list-item>
        <v-divider inset />
      </div>
    </v-list>
  </v-container>
  <v-dialog
    scrollable
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="modal.status"
  >
    <UpdateProvider
      :id="modal.id"
      :api_value_3="modal.api_value_3"
      :api_value_2="modal.api_value_2"
      :api_value_1="modal.api_value_1"
      :name="modal.name"
      :status="modal.status_method"
      @close="modal.status = false"
      @on-success="changeActive"
    />
  </v-dialog>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import providersService from "@/services/providers/providers.service";
import UpdateProvider from "@/components/modals/UpdateProvider.vue";

export default {
  name: "AdminProviders",
  components: { UpdateProvider, PageLoading, PageBar },
  data: () => ({
    loading: true,
    list: [],
    modal: {
      status: false,
      index: undefined,
      id: 0,
      status_method: 0,
      name: "",
      api_value_1: "",
      api_value_2: "",
      api_value_3: "",
    },
  }),
  methods: {
    changeActive() {
      this.loading = true;
      this.modal.status = false;
      this.getProviders();
    },
    getProviders() {
      providersService
        .getPaymentProviders()
        .then((res) => {
          const data = res.data;
          this.list = data.list;
          this.loading = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
        });
    },
    openModal(index) {
      const item = this.list[index];
      this.modal = {
        status: true,
        index: index,
        id: item.id,
        status_method: item.status,
        name: item.name,
        api_value_1: item.api_value_1,
        api_value_2: item.api_value_2,
        api_value_3: item.api_value_3,
        route: item.route,
      };
    },
  },
  beforeMount() {
    this.getProviders();
  },
};
</script>
