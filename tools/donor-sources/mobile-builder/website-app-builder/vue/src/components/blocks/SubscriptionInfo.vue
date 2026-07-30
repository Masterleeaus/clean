<template>
  <v-divider />
  <v-toolbar
    v-if="isPremium"
    color="transparent"
    class="cursor-pointer custom-container"
  >
    <v-list-item
      class="w-100 h-100"
      :to="uid ? `/private/profile/transactions/${uid}` : ''"
    >
      <v-list-item-title class="font-weight-medium" v-if="isActive">
        {{ $tr("project", "key_379") }}: {{ expireDate }}
      </v-list-item-title>
      <v-list-item-title class="font-weight-medium" v-else>
        {{ $tr("project", "key_380") }} {{ expireDate }}
      </v-list-item-title>
      <v-list-item-subtitle v-if="buildCount !== 0">
        {{ $tr("project", "key_391") }}: {{ remainingCount }}
      </v-list-item-subtitle>
      <v-list-item-subtitle v-else>
        {{ $tr("project", "key_391") }}: {{ $tr("project", "key_398") }}
      </v-list-item-subtitle>
    </v-list-item>
  </v-toolbar>
  <v-toolbar v-else color="transparent" class="cursor-pointer">
    <v-list-item
      class="w-100 h-100"
      @click="$store.commit('switchOpenPaymentModal')"
    >
      <template v-slot:prepend>
        <CrownIcon class="mr-5" size="28" />
      </template>
      <v-list-item-title class="font-weight-medium">
        {{ $tr("menu", "key_40") }}
      </v-list-item-title>
      <v-list-item-subtitle>
        {{ $tr("menu", "key_41") }}
      </v-list-item-subtitle>
      <template v-slot:append>
        <v-icon icon="mdi-arrow-right" />
      </template>
    </v-list-item>
  </v-toolbar>
</template>

<script>
export default {
  name: "SubscriptionInfo",
  props: {
    isPremium: {
      type: Boolean,
      required: true,
      default: false,
    },
    uid: {
      type: String,
      required: true,
      default: "",
    },
    isActive: {
      type: Boolean,
      required: true,
      default: false,
    },
    expireDate: {
      type: String,
      required: true,
      default: "",
    },
    buildCount: {
      type: Number,
      required: true,
      default: 0,
    },
    remainingCount: {
      type: Number,
      required: true,
      default: 0,
    },
  },
};
</script>
