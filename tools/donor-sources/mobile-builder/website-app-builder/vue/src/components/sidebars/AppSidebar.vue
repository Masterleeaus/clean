<template>
  <div
    class="d-flex justify-space-between align-center flex-column h-100"
    style="padding-top: 15px; padding-bottom: 12px"
  >
    <div class="d-flex flex-column ga-7 align-center">
      <img
        :src="$store.state.config.logo"
        height="34"
        alt="Logo"
        class="mb-5"
      />
      <div v-for="item in navigation">
        <v-tooltip :text="$tr('menu', item.name)" content-class="px-4">
          <template v-slot:activator="{ props }">
            <v-btn
              v-bind="props"
              icon=""
              size="40"
              density="comfortable"
              elevation="0"
              :class="`text-${item.color}`"
              :to="item.to"
              @click="!item.to ? $store.commit('switchOpenCreateModal') : null"
            >
              <component :is="item.icon"/>
            </v-btn>
          </template>
        </v-tooltip>
      </div>
    </div>
    <div class="d-flex flex-column align-center ga-7">
      <v-btn
        icon=""
        size="40"
        density="comfortable"
        elevation="0"
        @click="toggleTheme"
      >
        <MoonIcon class="text-blue-grey-lighten-1"/>
      </v-btn>
      <v-tooltip :text="$tr('menu', 'key_24')" content-class="px-4">
        <template v-slot:activator="{ props }">
          <UserAvatar v-bind="props"/>
        </template>
      </v-tooltip>
    </div>
  </div>
</template>

<script>
import AddIcon from "@/components/icons/AddIcon.vue";
import MessagesIcon from "@/components/icons/MessagesIcon.vue";
import AppsIcon from "@/components/icons/AppsIcon.vue";
import UserAvatar from "@/components/blocks/UserAvatar.vue";
import MoonIcon from "@/components/icons/MoonIcon.vue";
import DashboardIcon from "@/components/icons/DashboardIcon.vue";
export default {
  name: 'AppSidebar',
  components: {MoonIcon, UserAvatar},
  computed: {
    navigation() {
      return this.$store.state.user.admin ? this.list : this.list.slice(0, -1);
    }
  },
  data: () => ({
    list: [
      {
        name: "key_31",
        to: null,
        color: "success",
        icon: AddIcon
      },
      {
        name: "key_1",
        to: '/private/apps',
        color: "blue-grey-lighten-1",
        icon: AppsIcon
      },
      {
        name: "key_4",
        to: "/private/support",
        color: "blue-grey-lighten-1",
        icon: MessagesIcon
      },
      {
        name: "key_9",
        to: "/admin/dashboard",
        color: "blue-grey-lighten-1",
        icon: DashboardIcon
      },
    ]
  }),
  watch: {

  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
<script setup>
import { useTheme } from 'vuetify'
import {useStore} from "vuex";

const theme = useTheme();
const store = useStore();

function toggleTheme () {
  const val = theme.global.current.value.dark ? 'light' : 'dark';
  theme.global.name.value = val;
  store.commit('setTheme', val);
}
</script>
