<template>
  <v-avatar
    color="blue-grey-lighten-5"
    size="48"
    class="d-flex justify-center align-center flex-column"
    style="border-radius: 15px;"
  >
    <v-icon
      :icon="platform === 'android' ? 'mdi-android' : 'mdi-apple'"
      color="blue-grey-darken-1"
    />
    <div class="text-caption text-uppercase font-weight-medium text-blue-grey-darken-1">
      {{ file }}
    </div>
  </v-avatar>
</template>

<script>
export default {
  name: 'BuildAvatar',
  props: {
    platform: {
      type: String,
      required: true
    },
    file: {
      type: String,
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
