<template>
  <PageBar :title="$tr('admin', 'key_4')" is-back/>
  <PageLoading v-if="loading"/>
  <template v-else>
    <Placeholder v-if="!list.length">
      <template v-slot:icon>
        <MailStackIcon :size="56"/>
      </template>
      <template v-slot:title>
        {{ $tr('admin', 'key_60') }}
      </template>
      <template v-slot:subtitle>
        {{ $tr('admin', 'key_61') }}
      </template>
    </Placeholder>
    <v-container fluid v-else>
      <div class="w-100 d-flex justify-space-between align-center mb-3">
        <div class="font-weight-medium text-subtitle-2">
          {{ $tr('project', 'key_239') }} {{ total }}
        </div>
      </div>
      <v-list
        item-props
        density="comfortable"
      >
        <div v-for="(ticket) in list">
          <v-list-item
            lines="two"
            density="default"
            :to="`/admin/support/ticket/${ticket.uid}`"
          >
            <template v-slot:prepend>
              <v-avatar>
                <MailIcon :size="24"/>
              </v-avatar>
            </template>
            <v-list-item-title class="font-weight-bold">{{ ticket.title }}</v-list-item-title>
            <v-list-item-subtitle>{{ ticket.message.comment }}</v-list-item-subtitle>
            <template v-slot:append>
              <v-list-item-action>
                <v-chip
                  class="ml-3"
                  density="compact"
                  variant="tonal"
                  :color="getStatusColor(ticket.status)"
                >
                    <span class="text-caption">
                      {{ getStatusName(ticket.status) }}
                    </span>
                </v-chip>
              </v-list-item-action>
            </template>
          </v-list-item>
          <v-divider/>
        </div>
      </v-list>
      <div class="text-center mt-5" v-if="list.length !== total">
        <v-btn variant="text" color="primary" :loading="loadingMore" @click="getPendingList">
          {{ $tr('project', 'key_26') }}
        </v-btn>
      </div>
    </v-container>
  </template>
  <v-dialog
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="isOpenModal"
  >
    <CreateTicket @close="isOpenModal = false"/>
  </v-dialog>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import adminSupportService from "@/services/support/admin.support.service";

export default {
  name: 'CustomerTickets',
  components: {PageLoading, PageBar},
  data: () => ({
    loading: true,
    list: [],
    offset: 0,
    total: 0,
    isOpenModal: false,
    loadingMore: false
  }),
  computed: {

  },
  watch: {

  },
  methods: {
    getStatusColor(status) {
      if (!status) {
        return "warning";
      } else if (status === 1) {
        return "default";
      } else {
        return "success";
      }
    },
    getStatusName(status) {
      if (!status) {
        return this.$tr("project", "key_241");
      } else if (status === 1) {
        return this.$tr("admin", "key_58");
      } else {
        return this.$tr("project", "key_243");
      }
    },
    getPendingList() {
      this.loadingMore = true;
      adminSupportService.getUserTickets(this.$route.params.customer_id, this.offset).then((res) => {
        const data = res.data;
        this.list = this.list.concat(data.list)
        this.total = data.total;
        this.offset = this.offset + this.$store.state.offset;
        this.loadingMore = false;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.loadingMore = false;
      });
    }
  },
  beforeMount() {
    this.getPendingList();
  }
};
</script>
<script setup>
import Placeholder from "@/components/blocks/Placeholder.vue";
import MailStackIcon from "@/components/icons/MailStackIcon.vue";
import CreateTicket from "@/components/modals/CreateTicket.vue";
import MailIcon from "@/components/icons/MailIcon.vue";
</script>
