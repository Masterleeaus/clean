<template>
  <img
    :src="!src ? defaultIcon : src"
    class="iosRounded" alt=""
    :style="`width: ${size}px; height: ${size}px`"
  />
</template>

<script>
import defaultIcon from "@/assets/images/default_icon.svg";
export default {
  name: 'SquircleImage',
  props: {
    src: {
      type: String
    },
    size: {
      type: Number,
      default: 38
    },
  },
  data: () => ({
    defaultIcon: defaultIcon
  }),
  watch: {

  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
