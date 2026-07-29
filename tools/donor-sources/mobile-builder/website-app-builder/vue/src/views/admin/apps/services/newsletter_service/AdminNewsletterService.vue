<template>
  <PageBar :title="$tr('menu', 'key_2')"/>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-row class="mt-3">
      <v-col md="6" sm="12" cols="12" class="py-0 mb-3">
        <SubscribesCount :count="all" :subtitle="$tr('project', 'key_256')">
          <template v-slot:icon>
            <UsersIcon :size="36" class="text-primary"/>
          </template>
        </SubscribesCount>
      </v-col>
      <v-col md="6" sm="12" cols="12" class="py-0">
        <SubscribesCount :count="active" :subtitle="$tr('project', 'key_257')">
          <template v-slot:icon>
            <UserCheckIcon :size="36" class="text-primary"/>
          </template>
        </SubscribesCount>
      </v-col>
      <v-col md="12" sm="12" cols="12">
        <v-alert
          v-if="!is_prod"
          type="error"
          :text="$tr('admin', 'key_111')"
          variant="tonal"
          class="mb-5"
        ></v-alert>
        <div class="w-100 d-flex justify-space-between align-center mb-3">
          <div class="font-weight-medium text-subtitle-2">
            {{ $tr('project', 'key_350') }}
          </div>
          <v-btn variant="flat" color="primary" :disabled="!is_prod" @click="openCreateModal">
            {{ $tr('project', 'key_255') }}
          </v-btn>
        </div>
        <div v-if="!list.length" class="mt-5 text-body-1">
          {{ $tr('project', 'key_351') }}
        </div>
        <template v-else>
          <v-list item-props density="comfortable">
            <template v-for="(notification, index) in list">
              <v-list-item lines="one" density="default" class="pa-3">
                <template v-slot:prepend>
                  <RadioIcon :size="32" class="text-blue-grey-darken-1 mr-3"/>
                </template>
                <v-list-item-title>{{ notification.heading }}</v-list-item-title>
                <v-list-item-subtitle>{{ notification.queued_at }}: {{ notification.content }}</v-list-item-subtitle>
                <template v-slot:append>
                  <v-list-item-action>
                    <div class="text-center ml-4">
                      <v-list-item-title class="font-weight-bold mb-1">
                        {{ notification.successful }}
                      </v-list-item-title>
                      <v-list-item-subtitle class="text-caption text-uppercase">
                        {{ $tr('project', 'key_283') }}
                      </v-list-item-subtitle>
                    </div>
                    <div class="text-center ml-4">
                      <v-list-item-title class="font-weight-bold mb-1 text-success">
                        {{ notification.converted }}
                      </v-list-item-title>
                      <v-list-item-subtitle class="text-caption text-uppercase text-success">
                        {{ $tr('project', 'key_263') }}
                      </v-list-item-subtitle>
                    </div>
                    <div class="text-center ml-4">
                      <v-list-item-title class="font-weight-bold mb-1 text-red-accent-3">
                        {{ notification.errored }}
                      </v-list-item-title>
                      <v-list-item-subtitle class="text-caption text-uppercase text-red-accent-3">
                        {{ $tr('project', 'key_285') }}
                      </v-list-item-subtitle>
                    </div>
                  </v-list-item-action>
                </template>
              </v-list-item>
              <v-divider/>
            </template>
          </v-list>
          <div class="text-center mt-5" v-if="list.length !== total">
            <v-btn variant="text" color="primary" :loading="loadingMore" @click="getHistory">
              {{ $tr('project', 'key_26') }}
            </v-btn>
          </div>
        </template>
      </v-col>
    </v-row>
  </v-container>
  <v-dialog
    max-width="560"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="isOpenCreateModal"
  >
    <CreateNewsletter
      :uid="$route.params.uid"
      is-admin
      @close="isOpenCreateModal = false"
      @on-create="onCreateNotification"
    />
  </v-dialog>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import SubscribesCount from "@/components/blocks/subscriptions/SubscribesCount.vue";
import UsersIcon from "@/components/icons/UsersIcon.vue";
import UserCheckIcon from "@/components/icons/UserCheckIcon.vue";
import PushPending from "@/components/modals/PushPending.vue";
import RadioIcon from "@/components/icons/RadioIcon.vue";
import CreateNewsletter from "@/components/modals/CreateNewsletter.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'AdminNewsletterService',
  components: {CreateNewsletter, RadioIcon, PushPending, UserCheckIcon, UsersIcon, SubscribesCount, ScreenLock, PageBar},
  props: {
    premium: {
      type: String
    }
  },
  data: () => ({
    loading: true,
    all: 0,
    active: 0,
    is_prod: false,
    list: [],
    offset: 0,
    isOpenPendingModal: false,
    isOpenCreateModal: false,
    total: 0,
    loadingMore: false
  }),
  watch: {

  },
  methods: {
    openCreateModal() {
      this.isOpenCreateModal = true;
    },
    onCreateNotification() {
      this.loading = true;
      this.isOpenCreateModal = false;
      this.list = [];
      this.offset = 0;
      this.getSubscribes();
    },
    getSubscribes() {
      adminProjectsService.getTotalSubscribes(this.$route.params.uid).then((res) => {
        const data = res.data;
        this.all = data.all;
        this.active = data.active;
        this.is_prod = data.is_prod;
        if (!data.is_prod) {
          this.loading = false;
          return;
        }
        this.getHistory();
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    getHistory() {
      this.loadingMore = true;
      adminProjectsService.getPushHistory(this.$route.params.uid, this.offset).then((res) => {
        const data = res.data;
        this.offset = this.offset + 20;
        this.total = data.total;
        this.list = this.list.concat(data.list)
        this.loading = false;
        this.loadingMore = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.loadingMore = false;
      });
    }
  },
  beforeMount() {
    this.getSubscribes();
  }
};
</script>
