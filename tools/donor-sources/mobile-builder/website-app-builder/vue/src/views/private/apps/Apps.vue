<template>
  <PageBar :title="$tr('menu', 'key_1')" :is-menu="false"/>
  <PageLoading v-if="loading"/>
  <template v-else>
    <Placeholder v-if="!list.length">
      <template v-slot:icon>
        <FragmentIcon :size="56"/>
      </template>
      <template v-slot:title>
        {{ $tr('project', 'key_20') }}
      </template>
      <template v-slot:subtitle>
        {{ $tr('project', 'key_21') }}
      </template>
      <template v-slot:action>
        <v-btn variant="tonal" color="primary" @click="$store.commit('switchOpenCreateModal')">
          {{ $tr('project', 'key_2') }}
        </v-btn>
      </template>
    </Placeholder>
    <v-container fluid v-else>
      <v-list
        v-for="(app) in list"
        item-props
        lines
      >
        <v-list-item
          :to="`/private/apps/${app.uid}/main`"
          lines="two"
        >
          <template v-slot:prepend>
            <SquircleImage
              :src="app.icon"
              :size="48"
              class="mr-5"
            />
          </template>
          <v-list-item-title class="font-weight-medium">{{ app.name }}</v-list-item-title>
          <v-list-item-subtitle>{{ app.link }}</v-list-item-subtitle>
          <template v-slot:append>
            <v-chip density="comfortable" :color="!app.status ? 'default' : 'success'">
              {{ !app.status ? $tr('project', 'key_25') : $tr('project', 'key_19') }}
            </v-chip>
          </template>
        </v-list-item>
        <v-divider inset/>
      </v-list>
      <div class="text-center mt-5" v-if="list.length !== total">
        <v-btn variant="text" color="primary" :loading="loadingMore" @click="getProjectsList">
          {{ $tr('project', 'key_26') }}
        </v-btn>
      </div>
    </v-container>
  </template>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import Placeholder from "@/components/blocks/Placeholder.vue";
import FragmentIcon from "@/components/icons/FragmentIcon.vue";
import SquircleImage from "@/components/blocks/SquircleImage.vue";
import projectsService from "@/services/projects/projects.service";

export default {
  name: 'Apps',
  components: {SquircleImage, FragmentIcon, Placeholder, PageLoading, PageBar},
  data: () => ({
    loading: true,
    list: [],
    offset: 0,
    total: 0,
    loadingMore: false
  }),
  methods: {
    getProjectsList() {
      this.loadingMore = true;
      projectsService.getProjects(this.offset).then((res) => {
        const data = res.data;
        this.offset = this.offset + this.$store.state.offset;
        this.total = data.total;
        this.list = this.list.concat(data.list)
        this.loading = false;
        this.loadingMore = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
        this.loading = false;
        this.loadingMore = false;
      });
    }
  },
  beforeMount() {
    this.getProjectsList();
  }
};
</script>
