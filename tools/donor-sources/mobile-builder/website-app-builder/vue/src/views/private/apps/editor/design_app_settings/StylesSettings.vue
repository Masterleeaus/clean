<template>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-alert
      type="info"
      :text="$tr('project', 'key_339')"
      variant="tonal"
    ></v-alert>
    <v-table>
      <thead>
      <tr>
        <th class="text-left">
          {{ $tr('project', 'key_135') }}
        </th>
        <th class="text-right" width="100px">
          {{ $tr('project', 'key_136') }}
        </th>
      </tr>
      </thead>
      <tbody>
      <template v-if="!styles.length">
        <tr>
          <td colspan="2">
            {{ $tr('project', 'key_137') }}
          </td>
        </tr>
      </template>
      <template v-else>
        <tr v-for="(item, index) in styles">
          <td>.{{ item.name }}</td>
          <td class="text-right">
            <v-btn
              icon=""
              density="compact"
              flat
              color="red-accent-3"
              variant="text"
              :loading="index === loadingIndex"
              @click="removeItem(index)"
            >
              <DeleteMiniIcon :size="16"/>
            </v-btn>
          </td>
        </tr>
      </template>
      </tbody>
    </v-table>
    <div style="height: 100px"/>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" @click="isModalOpen = true">
      {{ $tr('project', 'key_134') }}
    </v-btn>
  </FixedFooter>
  <v-dialog
    scrollable
    max-width="500"
    v-model="isModalOpen"
  >
    <AddStyleClass :uid="$route.params.uid" @close="isModalOpen = false" @add-style="addDivToList"/>
  </v-dialog>
</template>

<script>
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import projectsService from "@/services/projects/projects.service";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import AddStyleClass from "@/components/modals/AddStyleClass.vue";
import DeleteMiniIcon from "@/components/icons/DeleteMiniIcon.vue";

export default {
  name: 'StylesSettings',
  components: {DeleteMiniIcon, AddStyleClass, FixedFooter, ScreenLock},
  data: () => ({
    loading: true,
    styles: [],
    isModalOpen: false,
    loadingIndex: undefined
  }),
  watch: {

  },
  methods: {
    getList() {
      this.loading = true;
      projectsService.getStylesList(this.$route.params.uid).then((res) => {
        this.styles = res.data;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    addDivToList(val) {
      this.styles.push({
        id: val.id,
        name: val.name,
      });
      this.$emit("preview-update");
    },
    removeItem(index) {
      const item = this.styles[index];
      this.loadingIndex = index;
      projectsService.removeDivForHide(item.id).then(() => {
        this.styles.splice(index, 1);
        this.loadingIndex = undefined;
        this.$emit("preview-update");
      }).catch(e => {
        this.loadingIndex = undefined;
        this.$store.commit('openSnackbar', e);
      });
    }
  },
  beforeMount() {
    this.getList();
  }
};
</script>
