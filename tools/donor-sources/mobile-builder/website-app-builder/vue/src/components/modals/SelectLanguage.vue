<template>
  <v-card>
    <v-toolbar color="transparent">
      <v-toolbar-title>
        {{ $tr('project', 'key_290') }}
      </v-toolbar-title>
      <v-spacer/>
      <v-btn
        icon="mdi-window-close"
        color="red-accent-3"
        density="default"
        @click="this.$emit('close')"
      />
    </v-toolbar>
    <v-container fluid>
      <v-text-field
        :label="$tr('project', 'key_291')"
        variant="outlined"
        autofocus
        density="comfortable"
        v-model="search"
      ></v-text-field>
    </v-container>
    <v-divider/>
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
      <v-list v-else>
        <v-list-item v-for="(lang) in filteredCodes" link @click="this.$emit('change', lang.code)">
          <v-list-item-title>{{ lang.name }}</v-list-item-title>
          <template v-slot:append>
            <v-list-item-action class="text-uppercase blue-grey-lighten-1">
              {{ lang.code }}
            </v-list-item-action>
          </template>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script>
import dataService from "@/services/data/data.service";

export default {
  name: 'SelectLanguage',
  data: () => ({
    search: "",
    loading: true,
    list: []
  }),
  computed: {
    filteredCodes () {
      const filterBy = (term) => {
        const termLowerCase = term.toLowerCase()
        return (person) =>
          Object.keys(person)
            .some(prop => person[prop].toLowerCase().indexOf(termLowerCase) !== -1)
      }
      return this.list.filter(filterBy(this.search));
    }
  },
  watch: {

  },
  methods: {
    getLanguages() {
      dataService.getIso().then((res) => {
        this.list = res.data;
        this.loading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      })
    }
  },
  beforeMount() {
    this.getLanguages();
  }
};
</script>
