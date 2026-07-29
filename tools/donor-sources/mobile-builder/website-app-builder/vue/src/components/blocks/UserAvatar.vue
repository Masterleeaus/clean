<template>
  <router-link to="/private/profile">
    <v-avatar
      variant="outlined"
      color="blue-grey-lighten-4"
      class="cursor-pointer"
    >
      <v-img
        :src="avatarSource"
        aspect-ratio="1"
        alt="John"
      ></v-img>
    </v-avatar>
  </router-link>
</template>

<script>
export default {
  name: 'UserAvatar',
  data: () => ({

  }),
  computed: {
    avatarSource: function () {
      return `https://api.dicebear.com/7.x/identicon/svg?seed=${this.$store.state.user.email}`;
    }
  },
  methods: {

  },
  beforeMount() {

  }
};
</script>
