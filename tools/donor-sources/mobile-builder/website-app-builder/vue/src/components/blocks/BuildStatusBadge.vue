<template>
  <v-chip density="comfortable" :color="color">
    <v-progress-circular
      v-if="!status"
      indeterminate
      size="12"
      width="1"
      class="mr-2"
    ></v-progress-circular>
    {{ label }}
  </v-chip>
</template>

<script>
export default {
  name: 'BuildStatusBadge',
  props: {
    status: {
      type: String,
      required: true
    },
    isFail: {
      type: Boolean,
      required: true
    }
  },
  data: () => ({

  }),
  computed: {
    label: function () {
      if (!this.status) {
        return this.$tr('project', 'key_194');
      } else if (this.status && !this.isFail) {
        return this.$tr('project', 'key_195');
      } else {
        return this.$tr('project', 'key_196');
      }
    },
    color: function () {
      if (!this.status) {
        return "default";
      } else if (this.status && !this.isFail) {
        return "success";
      } else {
        return "red-accent-3";
      }
    },
  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
