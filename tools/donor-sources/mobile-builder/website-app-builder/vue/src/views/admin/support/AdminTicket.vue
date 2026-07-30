<template>
  <PageBar is-back :title="loading ? $tr('project', 'key_245') : ticket.title">
    <template v-if="!loading" v-slot:action>
      <v-btn
        variant="tonal"
        :color="ticket.status !== 2 ? 'success' : 'warning'"
        density="default"
        :loading="isSwitchLoading"
        @click="switchTicketStatus"
      >
        {{ ticket.status !== 2 ? $tr('project', 'key_253') : $tr('project', 'key_254') }}
      </v-btn>
    </template>
  </PageBar>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <div
      v-for="(comment, index) in list"
      class="border rounded mb-3"
    >
      <div class="pa-4 d-flex justify-space-between align-center">
        <div class="d-flex justify-start align-center">
          <v-avatar
            :size="38"
            :color="!comment.admin ? 'blue-grey-lighten-5' : 'primary'"
            class="mr-3 white--text"
          >
            <UserIcon :size="22"/>
          </v-avatar>
          <div class="font-weight-medium">
            {{ !comment.admin ? $tr('admin', 'key_50') : $tr('project', 'key_247') }}
          </div>
        </div>
        <div class="d-flex justify-start align-center text-blue-grey-darken-1">
          <div class="mr-2">
            <ClockIcon :size="12"/>
          </div>
          <div class="text-caption">
            {{ comment.created }}
          </div>
        </div>
      </div>
      <v-divider/>
      <div class="pa-4">
        <div class="pa-4 text-body-1" style="white-space: pre-line">
          {{ comment.message }}
        </div>
        <div class="d-flex justify-end" v-if="comment.estimation">
          <v-rating
            :model-value="comment.estimation"
            color="warning"
            readonly
          ></v-rating>
        </div>
      </div>
    </div>
    <template v-if="ticket.status !== 2">
      <v-textarea
        class="mt-5"
        :label="$tr('project', 'key_237')"
        variant="outlined"
        density="comfortable"
        v-model="message"
      ></v-textarea>
      <div class="d-flex justify-end">
        <v-btn variant="flat" color="primary" :loading="isCreateLoading" @click="createComment">
          {{ $tr('project', 'key_238') }}
        </v-btn>
      </div>
    </template>
  </v-container>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import UserIcon from "@/components/icons/UserIcon.vue";
import ClockIcon from "@/components/icons/ClockIcon.vue";
import adminSupportService from "@/services/support/admin.support.service";

export default {
  name: 'Ticket',
  components: {ClockIcon, UserIcon, PageLoading, PageBar},
  data: () => ({
    loading: true,
    ticket: {},
    list: [],
    isCreateLoading: false,
    isSwitchLoading: false,
    message: ""
  }),
  methods: {
    getTicketDetail() {
      adminSupportService.getTicket(this.$route.params.ticket_uid).then((res) => {
        const data =res.data;
        this.list = data.list;
        this.ticket = data.ticket;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    switchTicketStatus() {
      this.isSwitchLoading = true;
      adminSupportService.switchTicketStatus(this.$route.params.ticket_uid).then(() => {
        this.isSwitchLoading = false;
        this.ticket.status = this.ticket.status !== 2 ? 2 : 0;
      }).catch(e => {
        this.isSwitchLoading = false;
        this.$store.commit('openSnackbar', e);
      })
    },
    createComment() {
      this.isCreateLoading = true;
      adminSupportService.createComment(this.$route.params.ticket_uid, this.message).then((res) => {
        const data = res.data;
        this.list.push({
          uid: data.uid,
          message: this.message,
          created: data.created,
          estimation: 0,
          admin: true
        });
        this.isCreateLoading = false;
        this.message = "";
      }).catch(e => {
        this.isCreateLoading = false;
        this.$store.commit('openSnackbar', e);
      })
    }
  },
  beforeMount() {
    this.getTicketDetail();
  }
};
</script>
