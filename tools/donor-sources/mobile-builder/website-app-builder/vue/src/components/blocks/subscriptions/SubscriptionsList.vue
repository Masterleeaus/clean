<template>
  <v-container fluid>
    <v-list item-props density="comfortable">
      <div v-for="subscribe in list">
        <v-list-item
          v-if="subscribe.type === 'subscription'"
          lines="two"
          density="default"
          :to="detailRoute ? `${detailRoute}/${subscribe.uid}` : ''"
        >
          <template v-slot:prepend>
            <SquircleImage :src="subscribe.app.icon" :size="48" class="mr-5" />
          </template>
          <v-list-item-title class="font-weight-medium">
            {{ subscribe.app.name }}
          </v-list-item-title>
          <v-list-item-subtitle>
            {{
              subscribe.is_active
                ? $tr("project", "key_379")
                : $tr("project", "key_380")
            }}: {{ subscribe.expires_at }}
          </v-list-item-subtitle>
          <v-list-item-subtitle v-if="subscribe.build_count !== 0">
            {{ $tr("project", "key_391") }}: {{ subscribe.remaining_count }}
          </v-list-item-subtitle>
          <template v-slot:append>
            <v-list-item-action>
              <div class="d-flex justify-end align-end flex-column">
                <div class="text-h6">
                  {{ subscribe.price }} {{ $store.state.config.currency }}
                </div>
                <v-chip
                  density="compact"
                  variant="tonal"
                  :color="subscribe.is_active ? 'success' : 'red-accent-3'"
                >
                  <span class="text-caption">
                    {{
                      subscribe.is_active
                        ? $tr("project", "key_377")
                        : $tr("project", "key_378")
                    }}
                  </span>
                </v-chip>
              </div>
            </v-list-item-action>
          </template>
        </v-list-item>
        <v-list-item
          v-if="subscribe.type === 'per_build'"
          lines="two"
          density="default"
          :to="detailRoute ? `${detailRoute}/${subscribe.uid}` : ''"
        >
          <template v-slot:prepend>
            <SquircleImage :src="subscribe.app.icon" :size="48" class="mr-5" />
          </template>
          <v-list-item-title class="font-weight-medium">
            {{ subscribe.app.name }}
          </v-list-item-title>
          <v-list-item-subtitle>
            {{ $tr("project", "key_391") }}: {{ subscribe.remaining_count }}
          </v-list-item-subtitle>
          <template v-slot:append>
            <v-list-item-action>
              <div class="d-flex justify-end align-end flex-column">
                <div class="text-h6">
                  {{ subscribe.price }} {{ $store.state.config.currency }}
                </div>
                <v-chip
                  density="compact"
                  variant="tonal"
                  :color="
                    subscribe.remaining_count > 0 ? 'success' : 'red-accent-3'
                  "
                >
                  <span class="text-caption">
                    {{
                      subscribe.remaining_count > 0
                        ? $tr("project", "key_377")
                        : $tr("project", "key_378")
                    }}
                  </span>
                </v-chip>
              </div>
            </v-list-item-action>
          </template>
        </v-list-item>
        <v-divider />
      </div>
    </v-list>
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
  </v-container>
</template>

<script>
import SquircleImage from "@/components/blocks/SquircleImage.vue";

export default {
  name: "SubscriptionsList",
  components: {
    SquircleImage,
  },
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
    detailRoute: {
      type: String,
      required: false,
      default: "/private/profile/transactions",
    },
  },
  emits: ["load-more"],
};
</script>
