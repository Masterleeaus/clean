<template>
  <div class="w-100 border rounded pa-4 d-flex align-center">
    <slot name="icon"></slot>
    <div class="d-flex flex-column ml-4">
      <div class="text-h6">{{ count }}</div>
      <div class="text-caption">{{ subtitle }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SubscribesCount",
  props: {
    count: {
      type: Number,
      required: true,
    },
    subtitle: {
      type: String,
      required: true,
    },
  },
  data: () => ({}),
  watch: {},
  methods: {},
  beforeMount() {},
};
</script>
