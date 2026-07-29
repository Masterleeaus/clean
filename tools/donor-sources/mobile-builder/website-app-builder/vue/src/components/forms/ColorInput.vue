<template>
  <v-text-field
    :label="label"
    variant="outlined"
    density="comfortable"
    readonly
    :value="modelValue"
    :model-value="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  >
    <template v-slot:append-inner>
      <v-menu
        v-model="picker"
        :close-on-content-click="false"
        location="bottom"
        transition="fab-transition"
      >
        <template v-slot:activator="{ props }">
          <v-avatar
            :color="modelValue"
            size="20"
            class="cursor-pointer border"
            v-bind="props"
          />
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
  </v-text-field>
</template>

<script>
export default {
  name: 'ColorInput',
  props: {
    label: {
      type: String,
      required: true
    },
    modelValue: {
      type: String,
      required: true
    }
  },
  data: () => ({
    picker: false
  }),
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
