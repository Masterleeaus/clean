<template>
  <ConfigManager>
    <NotificationManager>
      <router-view/>
    </NotificationManager>
  </ConfigManager>
</template>

<script setup>
import ConfigManager from "@/components/managers/ConfigManager.vue";
import NotificationManager from "@/components/managers/NotificationManager.vue";
import { useTheme } from 'vuetify'
import {useStore} from "vuex";

const theme = useTheme();
const store = useStore();

if (localStorage.getItem('theme')) {
  theme.global.name.value = localStorage.theme;
}
</script>
