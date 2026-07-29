<template>
  <v-col md="6" sm="12" cols="12">
    <div
      class="border rounded w-100 cursor-pointer pa-3 d-flex align-center"
      :style="platform === 'android' && `border-color: rgb(var(--v-theme-primary))!important`"
      @click="$emit('change_platform', 'android')"
    >
      <v-avatar size="48" color="blue-grey-lighten-5">
        <v-icon icon="mdi-android" size="30" color="#00de7a"/>
      </v-avatar>
      <div class="ml-3">
        <div class="text-body-1 font-weight-medium">
          {{ $tr("project", "key_158") }}
        </div>
        <div class="text-body-2">
          {{ $tr("project", "key_184") }}
        </div>
      </div>
    </div>
  </v-col>
  <v-col md="6" sm="12" cols="12">
    <div
      class="border rounded w-100 cursor-pointer pa-3 d-flex align-center"
      :style="platform === 'ios' && `border-color: rgb(var(--v-theme-primary))!important`"
      @click="$emit('change_platform', 'ios')"
    >
      <v-avatar size="48" color="blue-grey-lighten-5">
        <v-icon icon="mdi-apple" size="30" color="black"/>
      </v-avatar>
      <div class="ml-3">
        <div class="text-body-1 font-weight-medium">
          {{ $tr("project", "key_159") }}
        </div>
        <div class="text-body-2">
          {{ $tr("project", "key_185") }}
        </div>
      </div>
    </div>
  </v-col>
</template>

<script>
export default {
  name: 'PlatformPicker',
  props: {
    platform: {
      type: String,
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
