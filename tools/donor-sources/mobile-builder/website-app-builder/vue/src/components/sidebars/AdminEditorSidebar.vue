<template>
  <v-navigation-drawer permanent :width="280">
    <v-toolbar color="transparent">
      <v-list-item class="w-100 h-100" lines="one">
        <template v-slot:prepend>
          <v-skeleton-loader
            v-if="loading"
            height="34"
            width="34"
            class="rounded-lg mr-3"
          />
          <SquircleImage v-else :src="project.icon" class="mr-3" size="34" />
        </template>
        <div v-if="loading">
          <v-skeleton-loader
            class="mb-3 w-50 rounded-md"
            style="height: 12px"
          />
          <v-skeleton-loader class="w-100 rounded-md" style="height: 8px" />
        </div>
        <template v-else>
          <v-list-item-title class="font-weight-medium">{{
            project.name
          }}</v-list-item-title>
          <v-list-item-subtitle class="text-caption">{{
            project.link
          }}</v-list-item-subtitle>
        </template>
        <template v-slot:append>
          <v-menu location="center">
            <template v-slot:activator="{ props }">
              <v-btn icon="mdi-dots-vertical" v-bind="props" />
            </template>
            <v-list>
              <v-list-item :to="`/admin/customers/${project.user_id}/profile`">
                <v-list-item-title>
                  {{ $tr("admin", "key_119") }}
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
      </v-list-item>
    </v-toolbar>
    <v-divider />
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr("menu", "key_16") }}
      </v-list-subheader>
      <v-list-item
        nav
        v-for="item in settings"
        exact
        color="primary"
        density="comfortable"
        :to="item.to"
      >
        <template v-slot:prepend>
          <component :is="item.icon" size="22" class="mr-3 text-primary" />
        </template>
        <v-list-item-title
          class="text-body-1 font-weight-regular line"
          style="line-height: 1.2rem"
        >
          {{ $tr("menu", item.name) }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
    <v-list nav class="pt-0 pb-0">
      <v-list-subheader class="font-weight-medium">
        {{ $tr("menu", "key_38") }}
      </v-list-subheader>
      <v-list-item
        nav
        v-for="item in services"
        exact
        color="primary"
        density="comfortable"
        :to="item.to"
      >
        <template v-slot:prepend>
          <component :is="item.icon" size="22" class="mr-3 text-primary" />
        </template>
        <v-list-item-title
          class="text-body-1 font-weight-regular line"
          style="line-height: 1.2rem"
        >
          {{ $tr("menu", item.name) }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
    <template v-slot:append v-if="!loading">
      <v-dialog v-model="openGiftModal" max-width="600">
        <MakeGift 
          v-if="openGiftModal" 
          :app-uid="this.$route.params.uid"
          :user-id="project.user_id" 
          @close="openGiftModal = false" 
          @success="giftSuccess"
        />
      </v-dialog>
      <v-divider v-if="!isPremium" />
      <v-toolbar color="transparent" class="cursor-pointer">
        <v-list-item v-if="!isPremium" link class="w-100 h-100" @click="openGiftModal = true">
          <template v-slot:prepend>
            <v-icon icon="mdi-gift" />
          </template>
          <v-list-item-title class="font-weight-medium">
            {{ $tr("admin", "key_171") }}
          </v-list-item-title>
        </v-list-item>
      </v-toolbar>
      <v-divider />
      <v-toolbar color="transparent">
        <v-list-item class="w-100 h-100">
          <template v-slot:prepend>
            <v-icon :icon="subIcon" :color="subColor" />
          </template>
          <v-list-item-title class="font-weight-medium">
            {{ subTitle }}
          </v-list-item-title>
          <v-list-item-subtitle>
            {{ subUntil }}
          </v-list-item-subtitle>
        </v-list-item>
      </v-toolbar>
    </template>
  </v-navigation-drawer>
</template>

<script>
import HomeIcon from "@/components/icons/HomeIcon.vue";
import PaletteIcon from "@/components/icons/PaletteIcon.vue";
import CompassIcon from "@/components/icons/CompassIcon.vue";
import GlobeIcon from "@/components/icons/GlobeIcon.vue";
import ImageIcon from "@/components/icons/ImageIcon.vue";
import CheckDeviceIcon from "@/components/icons/CheckDeviceIcon.vue";
import PushIcon from "@/components/icons/PushIcon.vue";
import KeySquareIcon from "@/components/icons/KeySquareIcon.vue";
import LockIcon from "@/components/icons/LockIcon.vue";
import CrownIcon from "@/components/icons/CrownIcon.vue";
import SquircleImage from "@/components/blocks/SquircleImage.vue";
import DeviceStars from "@/components/icons/DeviceStars.vue";
import MakeGift from "@/components/modals/MakeGift.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: "AdminEditorSidebar",
  components: { SquircleImage, CrownIcon, MakeGift },
  data: () => ({
    loading: true,
    project: {
      name: "",
      icon: null,
      link: "",
      subscribe: 0,
      subscribe_uid: "",
      subscribe_date: "",
      user_id: 0,
    },
    settings: [
      {
        name: "key_11",
        to: "main",
        icon: HomeIcon,
      },
      {
        name: "key_12",
        to: "design",
        icon: PaletteIcon,
      },
      {
        name: "key_39",
        to: "permissions",
        icon: LockIcon,
      },
      {
        name: "key_22",
        to: "navigation",
        icon: CompassIcon,
      },
      {
        name: "key_23",
        to: "localization",
        icon: GlobeIcon,
      },
      {
        name: "key_42",
        to: "splashscreen",
        icon: DeviceStars,
      },
      {
        name: "key_43",
        to: "assets",
        icon: ImageIcon,
      },
    ],
    services: [
      {
        name: "key_15",
        to: "build",
        icon: CheckDeviceIcon,
      },
      {
        name: "key_2",
        to: "push",
        icon: PushIcon,
      },
      {
        name: "key_25",
        to: "signing",
        icon: KeySquareIcon,
      },
    ],
    openGiftModal: false,
  }),
  computed: {
    isPremium: function () {
      return !!this.project.subscribe;
    },
    subIcon: function () {
      if (this.isPremium) {
        return "mdi-check-circle-outline";
      } else {
        return "mdi-cancel";
      }
    },
    subTitle: function () {
      if (this.isPremium) {
        return this.$tr("admin", "key_112");
      } else {
        return this.$tr("admin", "key_113");
      }
    },
    subUntil: function () {
      if (this.isPremium && this.project.subscribe_date) {
        return `${this.$tr("admin", "key_114")} ${this.project.subscribe_date}`;
      } else {
        return this.$tr("admin", "key_115");
      }
    },
    subColor: function () {
      if (this.isPremium) {
        return "success";
      } else {
        return "red-accent-3";
      }
    },
  },
  watch: {},
  methods: {
    getAppDetail(isNotLoading) {
      if (!isNotLoading) {
        this.loading = true;
      }
      adminProjectsService
        .getProject(this.$route.params.uid)
        .then((res) => {
          this.project = res.data;
          this.loading = false;
        })
        .catch((e) => {
          this.$store.commit("openSnackbar", e);
          this.$router.push({ path: "/private/apps", replace: true });
        });
    },
    
    giftSuccess() {
      this.openGiftModal = false;
      this.getAppDetail(true);
    },
  },
  beforeMount() {
    this.getAppDetail();
  },
};
</script>
