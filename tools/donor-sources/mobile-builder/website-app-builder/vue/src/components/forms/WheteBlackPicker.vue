<template>
  <div class="rounded-lg border cursor-pointer" style="height: 96px">
    <div
      class="d-flex justify-center align-center w-100 rounded-t-lg"
      style="background: #ffffff; height: 48px"
      @click="this.$emit('change', true)"
    >
      <DoneIcon v-if="isDark" :size="20" class="text-black"/>
    </div>
    <div
      class="d-flex justify-center align-center w-100 rounded-b-lg text-white"
      style="background: #000000; height: 48px"
      @click="this.$emit('change', false)"
    >
      <DoneIcon v-if="!isDark" :size="20"/>
    </div>
  </div>
</template>

<script>
import DoneIcon from "@/components/icons/DownloadIcon.vue";

export default {
  name: 'WhiteBlackPicker',
  components: {DoneIcon},
  props: {
    isDark: {
      type: Boolean,
      required: true
    },
  },
  data: () => ({

  }),
  watch: {

  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
