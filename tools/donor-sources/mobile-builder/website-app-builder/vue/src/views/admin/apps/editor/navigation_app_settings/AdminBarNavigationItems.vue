<template>
  <ScreenLock
    class="loader-page-container"
    v-if="loading"
    :is-secondary="true"
  />
  <v-container fluid v-else>
    <v-list
      item-props
      density="comfortable"
    >
      <v-list-item v-if="!list.length">
        <template v-slot:prepend>
          <LinkMiniIcon :size="16" class="text-blue-grey-darken-1 mr-3"/>
        </template>
        <v-list-item-title>{{ $tr('project', 'key_63') }}</v-list-item-title>
      </v-list-item>
      <template v-else v-for="(nav, index) in list">
        <v-list-item
          lines="two"
          density="default"
        >
          <template v-slot:prepend>
            <v-avatar size="38" color="blue-grey-lighten-5 text-blue-grey-darken-2">
              <ion-icon :name="nav.icon"></ion-icon>
            </v-avatar>
          </template>
          <v-list-item-title>{{ nav.name }}</v-list-item-title>
          <v-list-item-subtitle>{{ actions[nav.type].title }}</v-list-item-subtitle>
          <template v-slot:append>
            <v-list-item-action>
              <v-btn
                icon=""
                color="primary"
                density="comfortable"
                flat
                variant="text"
                @click="openEditForm(index)"
              >
                <EditMiniIcon :size="16"/>
              </v-btn>
              <v-btn
                icon=""
                color="red-accent-3"
                density="comfortable"
                flat
                variant="text"
                :loading="index === removeIndex"
                @click="removeItem(index)"
              >
                <DeleteIcon :size="16"/>
              </v-btn>
            </v-list-item-action>
          </template>
        </v-list-item>
        <v-divider/>
      </template>
    </v-list>
  </v-container>
  <FixedFooter
    v-if="!loading"
    ref="footer"
  >
    <v-btn color="primary" variant="flat" @click="openCreateForm">
      {{ $tr('project', 'key_53') }}
    </v-btn>
  </FixedFooter>
  <v-dialog
    scrollable
    max-width="680"
    :close-on-content-click="false"
    :persistent="true"
    :no-click-animation="true"
    v-model="isModalOpen"
  >
    <CreateLink
      :uid="$route.params.uid"
      target="bar"
      :initial-action-type="edit.actionType"
      :initial-icon="edit.icon"
      :initial-id="edit.id"
      :initial-name="edit.name"
      :initial-value="edit.value"
      is-admin
      @close="isModalOpen = false"
      @create-nav="addToList"
      @update-nav="editList"
    />
  </v-dialog>
</template>

<script>
import ScreenLock from "@/components/blocks/ScreenLock.vue";
import FixedFooter from "@/components/blocks/FixedFooter.vue";
import CreateLink from "@/components/modals/CreateLink.vue";
import EditMiniIcon from "@/components/icons/EditMiniIcon.vue";
import RefreshMiniIcon from "@/components/icons/RefreshMiniIcon.vue";
import DeleteIcon from "@/components/icons/DeleteIcon.vue";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'AdminBarNavigationItems',
  components: {DeleteIcon, RefreshMiniIcon, EditMiniIcon, CreateLink, FixedFooter, ScreenLock},
  data: () => ({
    loading: true,
    list: [],
    isModalOpen: false,
    removeIndex: undefined,
    edit: {
      id: 0,
      name: "",
      icon: "",
      actionType: 0,
      value: "",
      index: 0
    }
  }),
  computed: {
    actions: function () {
      return [
        {
          title: this.$tr('project', 'key_57'),
          value: 0
        },
        {
          title: this.$tr('project', 'key_58'),
          value: 1
        },
        {
          title: this.$tr('project', 'key_59'),
          value: 2
        },
        {
          title: this.$tr('project', 'key_60'),
          value: 3
        },
        {
          title: this.$tr('project', 'key_298'),
          value: 4
        },
      ];
    }
  },
  watch: {

  },
  methods: {
    getItems() {
      adminProjectsService.getBarNavigation(this.$route.params.uid).then((res) => {
        this.list = res.data;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    addToList(val) {
      this.list.push(val);
      this.isModalOpen = false;
      this.$emit("preview-update");
    },
    editList(val) {
      this.list[this.edit.index] = val;
      this.isModalOpen = false;
    },
    openEditForm(index) {
      const item = this.list[index];
      this.edit = {
        id: item.id,
        name: item.name,
        icon: item.icon,
        actionType: item.type,
        value: item.link,
        index: index
      }
      this.isModalOpen = true;
    },
    openCreateForm() {
      this.edit = {
        id: 0,
        name: "",
        icon: "",
        actionType: 0,
        value: "",
        index: 0
      }
      this.isModalOpen = true;
    },
    removeItem(index) {
      this.removeIndex = index;
      const id = this.list[index].id;
      adminProjectsService.removeNavigationItem(id, "bar").then(() => {
        this.list.splice(index, 1);
        this.removeIndex = undefined;
        this.$emit("preview-update");
      }).catch(e => {
        this.removeIndex = undefined;
        this.$store.commit('openSnackbar', e);
      })
    }
  },
  beforeMount() {
    this.getItems();
  }
};
</script>
