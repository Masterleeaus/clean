<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr('project', !initialName.length ? 'key_54' : 'key_67') }}
      </v-toolbar-title>
      <v-spacer/>
      <v-btn
        icon="mdi-window-close"
        color="red-accent-3"
        density="default"
        @click="this.$emit('close')"
      />
    </v-toolbar>
    <v-divider/>
    <v-container fluid>
      <v-row class="mt-3">
        <v-col md="6" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="$tr('project', 'key_55')"
            variant="outlined"
            density="comfortable"
            autofocus
            v-model="name"
          ></v-text-field>
        </v-col>
        <v-col md="6" sm="12" cols="12" class="py-0">
          <v-select
            :label="$tr('project', 'key_56')"
            variant="outlined"
            density="comfortable"
            :items="actions"
            v-model="actionType"
          ></v-select>
        </v-col>
        <v-col md="12" sm="12" cols="12" class="py-0">
          <v-text-field
            :label="label"
            :disabled="actionType === 2"
            variant="outlined"
            density="comfortable"
            v-model="value"
          ></v-text-field>
        </v-col>
      </v-row>
    </v-container>
    <v-divider/>
    <v-container fluid class="pb-0 mb-3">
      <v-text-field
        :label="$tr('project', 'key_299')"
        variant="outlined"
        density="comfortable"
        hide-details
        v-model="search"
      ></v-text-field>
    </v-container>
    <v-card-text style="height: 400px; padding: 0; max-height: 400px">
      <div v-if="loading" class="h-100 w-100 d-flex justify-center align-center">
        <v-progress-circular
          indeterminate
          color="blue-grey-lighten-1"
          bg-color="blue-grey-lighten-5"
          size="36"
          width="3"
        ></v-progress-circular>
      </div>
      <v-container fluid class="icons_list" v-else>
        <div class="icon-results">
          <div
            v-for="(icon, i) in filterIcons"
            :key="`${i}_icon_select`"
            class="icon_preview border"
            :style="icon.name === selectedIcon ? 'border-color: rgb(var(--v-theme-primary), 1)!important' : ''"
            @click="setIcon(icon.name)"
          >
            <ion-icon :name="icon.name"></ion-icon>
          </div>
        </div>
      </v-container>
    </v-card-text>
    <v-divider/>
    <v-container fluid class="d-flex justify-end align-center">
      <v-btn  color="primary" variant="flat" :loading="isUpdate" @click="saveItem">
        {{ $tr('project', 'key_173') }}
      </v-btn>
    </v-container>
  </v-card>
</template>

<script>
import dataService from "@/services/data/data.service";
import projectsService from "@/services/projects/projects.service";
import adminProjectsService from "@/services/projects/admin.projects.service";

export default {
  name: 'CreateLink',
  props: {
    initialName: {
      type: String,
      default: ""
    },
    initialIcon: {
      type: String,
      default: ""
    },
    initialActionType: {
      type: Number,
      default: 0
    },
    initialId: {
      type: Number,
      default: 0
    },
    initialValue: {
      type: String,
      default: ""
    },
    uid: {
      type: String,
      required: true
    },
    target: {
      type: String,
      required: true
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  data: () => ({
    name: "",
    actionType: 0,
    value: "",
    search: "",
    loading: true,
    icons: [],
    selectedIcon: "accessibility",
    isUpdate: false
  }),
  computed: {
    filterIcons() {
      const termLowerCase = this.search.toLowerCase();
      const filteredIcons = this.icons.filter(item => {
        return item.tags.some(obj => obj.toLowerCase().indexOf(termLowerCase) !== -1);
      });
      return filteredIcons.slice(0, 100);
    },
    label: function () {
      if (this.actionType === 0 || this.actionType === 1) {
        return this.$tr('project', 'key_61')
      } else if (this.actionType === 2) {
        return this.$tr('project', 'key_48')
      } else if (this.actionType === 3) {
        return this.$tr('auth', 'key_5')
      } else {
        return this.$tr('project', 'key_301')
      }
    },
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
        }
      ];
    },
  },
  watch: {

  },
  methods: {
    getIconsList() {
      dataService.getIcons().then((res) => {
        this.icons = res.data.icons;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    setIcon(val) {
      this.selectedIcon = val;
    },
    saveItem() {
      if (this.initialId) {
        this.updateItem();
      } else {
        this.createItem();
      }
    },
    createItem() {
      this.isUpdate = true;
      if (this.isAdmin) {
        adminProjectsService.createNavigationItem(this.uid, this.target, {
          name: this.name,
          action_type: this.actionType,
          icon: this.selectedIcon,
          link: this.value
        }).then((res) => {
          const data = res.data;
          this.$emit('create-nav', {
            id: data.id,
            name: this.name,
            type: this.actionType,
            icon: this.selectedIcon,
            link: this.value
          });
          this.isUpdate = false;
        }).catch(e => {
          this.isUpdate = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.createNavigationItem(this.uid, this.target, {
          name: this.name,
          action_type: this.actionType,
          icon: this.selectedIcon,
          link: this.value
        }).then((res) => {
          const data = res.data;
          this.$emit('create-nav', {
            id: data.id,
            name: this.name,
            type: this.actionType,
            icon: this.selectedIcon,
            link: this.value
          });
          this.isUpdate = false;
        }).catch(e => {
          this.isUpdate = false;
          this.$store.commit('openSnackbar', e);
        });
      }
    },
    updateItem() {
      this.isUpdate = true;
      if (this.isAdmin) {
        adminProjectsService.updateNavigationItem(this.initialId, this.target, {
          name: this.name,
          action_type: this.actionType,
          icon: this.selectedIcon,
          link: this.value
        }).then((res) => {
          const data = res.data;
          this.$emit('update-nav', {
            id: this.initialId,
            name: this.name,
            type: this.actionType,
            icon: this.selectedIcon,
            link: this.value
          });
          this.isUpdate = false;
        }).catch(e => {
          this.isUpdate = false;
          this.$store.commit('openSnackbar', e);
        });
      } else {
        projectsService.updateNavigationItem(this.initialId, this.target, {
          name: this.name,
          action_type: this.actionType,
          icon: this.selectedIcon,
          link: this.value
        }).then((res) => {
          const data = res.data;
          this.$emit('update-nav', {
            id: this.initialId,
            name: this.name,
            type: this.actionType,
            icon: this.selectedIcon,
            link: this.value
          });
          this.isUpdate = false;
        }).catch(e => {
          this.isUpdate = false;
          this.$store.commit('openSnackbar', e);
        });
      }
    }
  },
  beforeMount() {
    if (this.initialId) {
      this.name = this.initialName;
      this.selectedIcon = this.initialIcon;
      this.actionType = this.initialActionType;
      this.value = this.initialValue;
    }
    this.getIconsList();
  }
};
</script>
