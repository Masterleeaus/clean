<template>
  <v-container fluid>
    <v-row>
      <v-col md="3" sm="12" cols="12">
        <div class="border rounded pa-4">
          <div class="text-h6">
            {{ subscribe.created_at }}
          </div>
          <div class="text-body-2 text-blue-grey-darken-1">
            {{ $tr("project", "key_22") }}
          </div>
        </div>
      </v-col>
      <v-col v-if="subscribe.type === 'subscription'" md="3" sm="12" cols="12">
        <div class="border rounded pa-4">
          <div class="text-h6">
            {{ subscribe.expires_at }}
          </div>
          <div class="text-body-2 text-blue-grey-darken-1">
            {{
              subscribe.is_active
                ? $tr("project", "key_379")
                : $tr("project", "key_380")
            }}
          </div>
        </div>
      </v-col>
      <v-col v-if="subscribe.type === 'per_build' || (subscribe.type === 'subscription' && subscribe.remaining_count > 0)" md="3" sm="12" cols="12">
        <div class="border rounded pa-4">
          <div class="text-h6 text-capitalize">
            {{ subscribe.remaining_count }}
          </div>
          <div class="text-body-2 text-blue-grey-darken-1">
            {{
              $tr("project", "key_391")
            }}
          </div>
        </div>
      </v-col>
      <v-col md="3" sm="12" cols="12">
        <div class="border rounded pa-4">
          <div
            :class="`text-h6 ${isActive ? 'text-success' : 'text-red-accent-3'}`"
          >
            {{
              isActive
                ? $tr("project", "key_377")
                : $tr("project", "key_378")
            }}
          </div>
          <div class="text-body-2 text-blue-grey-darken-1">
            {{ $tr("project", "key_381") }}
          </div>
        </div>
      </v-col>
      <v-col md="12" sm="12" cols="12">
        <div class="border rounded">
          <v-list-item
            :to="appRoute ? `${appRoute}/${subscribe.app.uid}/main` : ''"
            lines="two"
          >
            <template v-slot:prepend>
              <SquircleImage
                :src="subscribe.app.icon"
                :size="48"
                class="mr-5"
              />
            </template>
            <v-list-item-title class="font-weight-medium">{{
              subscribe.app.name
            }}</v-list-item-title>
            <v-list-item-subtitle>{{
              subscribe.app.link
            }}</v-list-item-subtitle>
            <template v-slot:append>
              <v-list-item-action>
                <v-icon icon="mdi-chevron-right" />
              </v-list-item-action>
            </template>
          </v-list-item>
        </div>
      </v-col>
      <v-col md="12" sm="12" cols="12">
        <div class="d-flex justify-space-between align-center mb-3">
          <div class="text-body-1 font-weight-medium">
            {{ $tr("project", "key_382") }}
          </div>
          <v-btn
            v-if="subscribe.type === 'subscription'"
            variant="flat"
            color="red-accent-3"
            :disabled="!subscribe.is_active"
            @click="$emit('cancel-subscription')"
          >
            {{ $tr("project", "key_383") }}
          </v-btn>
        </div>
        <TransactionsList
          :list="list"
          :total="total"
          :loading-more="loadingMore"
          :show-load-more="showLoadMore"
          :is-admin="isAdmin"
          @load-more="$emit('load-more')"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import SquircleImage from "@/components/blocks/SquircleImage.vue";
import TransactionsList from "@/components/blocks/subscriptions/TransactionsList.vue";

export default {
  name: "SubscriptionDetails",
  components: {
    SquircleImage,
    TransactionsList,
  },
  props: {
    subscribe: {
      type: Object,
      required: true,
      default: () => ({
        uid: "",
        created_at: "",
        expires_at: "",
        price: "",
        app: {
          name: "",
          uid: "",
          icon: "",
          link: "",
        },
        remaining_count: 0,
        type: "subscription",
        is_active: false,
      }),
    },
    list: {
      type: Array,
      required: true,
      default: () => [],
    },
    total: {
      type: Number,
      required: false,
      default: 0,
    },
    loadingMore: {
      type: Boolean,
      required: false,
      default: false,
    },
    showLoadMore: {
      type: Boolean,
      required: false,
      default: true,
    },
    appRoute: {
      type: String,
      required: false,
      default: "/private/apps",
    },
    isAdmin: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  computed: {
    isActive() {
      if (this.subscribe.type === "subscription") {
        return this.subscribe.is_active;
      }
      if (this.subscribe.type === "per_build") {
        return this.subscribe.remaining_count > 0;
      }
      return false;
    }
  },
  emits: ["load-more", "cancel-subscription"],
};
</script>
