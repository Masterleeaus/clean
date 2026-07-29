<template>
  <v-avatar
    v-for="(item) in colors"
    :color="item"
    size="40"
    class="mr-2 mb-2 cursor-pointer border"
    @click="this.$emit('change', item)"
  >
    <DoneIcon size="20" v-if="seed === item"/>
  </v-avatar>
  <v-avatar
    v-if="isCustomSeed"
    size="40"
    :color="seed"
    class="mr-2 mb-2 cursor-pointer border"
  >
    <DoneIcon size="20"/>
  </v-avatar>
  <v-menu
    v-model="picker"
    :close-on-content-click="false"
    location="center"
    transition="fab-transition"
  >
    <template v-slot:activator="{ props }">
      <v-avatar
        size="40"
        class="mr-2 mb-2 cursor-pointer border"
        v-bind="props"
      >
        <ColorPickerIcon :size="18"/>
      </v-avatar>
    </template>
    <v-card min-width="300" rounded elevation="1">
      <v-color-picker
        mode="hex"
        :modes="['hex']"
        @update:model-value="changeCustomColor"
      />
    </v-card>
  </v-menu>
</template>

<script>
import DoneIcon from "@/components/icons/DownloadIcon.vue";
import ColorPickerIcon from "@/components/icons/ColorPickerIcon.vue";
export default {
  name: 'ColorSeedPicker',
  components: {DoneIcon, ColorPickerIcon},
  props: {
    seed: {
      type: String,
      required: true
    },
  },
  data: () => ({
    colors: [
      "#F44336",
      "#E91E63",
      "#673AB7",
      "#3F51B5",
      "#2196F3",
      "#4CAF50",
      "#009688",
      "#FFC107",
      "#000000",
      "#ffffff"
    ],
    picker: false
  }),
  computed: {
    isCustomSeed: function () {
      return !this.colors.includes(this.seed);
    }
  },
  watch: {

  },
  methods: {
    changeCustomColor(val) {
      this.$emit('change', val);
    }
  },
  beforeMount() {

  }
};
</script>
