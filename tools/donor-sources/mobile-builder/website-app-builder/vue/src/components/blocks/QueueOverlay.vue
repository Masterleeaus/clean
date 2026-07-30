<template>
  <div class="preview-overlay pa-8 text-center">
    <div
      class="rounded border text-center pa-4 d-flex flex-column justify-center"
      style="border-color: #ffffff!important; border-width: 2px!important; width: 60%"
    >
      <div class="text-h2 font-weight-medium mb-2">{{ position }}</div>
      <div class="body-1">{{ $tr("project", "key_314") }}</div>
    </div>
    <div class="d-flex flex-column justify-center">
      <div class="text-body-1 font-weight-bold mb-2 mt-8">
        {{ $tr("project", "key_315") }}
      </div>
      <div class="text-body-2 mb-8">
        {{ $tr("project", "key_316") }}
      </div>
    </div>
    <v-btn variant="tonal" @click="$emit('cancel-session')">
      {{ $tr("project", "key_354") }}
    </v-btn>
  </div>
</template>

<script>
export default {
  name: 'QueueOverlay',
  props: {
    position: {
      type: Number,
      required: true
    }
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
