<template>
  <div>
    <v-list v-if="list.length" item-props density="comfortable">
      <div v-for="transaction in list">
        <v-list-item
          lines="two"
          density="default"
          :to="
            detailRoute && transaction.subscription_uid
              ? `${detailRoute}/${transaction.subscription_uid}`
              : undefined
          "
          :class="{
            'cursor-pointer': detailRoute && transaction.subscription_uid,
          }"
        >
          <template v-slot:prepend>
            <v-avatar size="38" :image="transaction.method.logo" />
          </template>
          <v-list-item-title>{{ transaction.method.name }}</v-list-item-title>
          <v-list-item-subtitle>
            ID: {{ transaction.uid }}
            <template v-if="isAdmin && transaction.external_uid">
              / {{ $tr("admin", "key_116") }}: {{ transaction.external_uid }}
            </template>
          </v-list-item-subtitle>
          <template v-slot:append>
            <v-list-item-action>
              <div class="d-flex justify-end align-end flex-column">
                <v-list-item-title class="font-weight-medium">
                  {{ transaction.amount }} {{ $store.state.config.currency }}
                </v-list-item-title>
                <v-list-item-subtitle>{{
                  transaction.created_at
                }}</v-list-item-subtitle>
              </div>
            </v-list-item-action>
          </template>
        </v-list-item>
        <v-divider />
      </div>
    </v-list>
    <div v-else>
      {{ $tr("project", "key_384") }}
    </div>
    <div class="text-center mt-5" v-if="showLoadMore && list.length !== total">
      <v-btn
        variant="text"
        color="primary"
        :loading="loadingMore"
        @click="$emit('load-more')"
      >
        {{ $tr("project", "key_26") }}
      </v-btn>
    </div>
  </div>
</template>

<script>
export default {
  name: "TransactionsList",
  props: {
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
    isAdmin: {
      type: Boolean,
      required: false,
      default: false,
    },
    detailRoute: {
      type: String,
      required: false,
      default: "",
    },
  },
  emits: ["load-more"],
};
</script>
