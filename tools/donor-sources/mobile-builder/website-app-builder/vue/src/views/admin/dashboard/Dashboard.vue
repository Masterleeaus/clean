<template>
  <PageBar :title="$tr('admin', 'key_1')"/>
  <PageLoading v-if="loading"/>
  <v-container fluid v-else>
    <v-alert
      v-for="(notification) in notifications"
      type="error"
      :text="notification"
      variant="tonal"
      class="mb-3"
    ></v-alert>
    <v-row class="mt-3">
      <v-col md="3" sm="12" cols="12" class="py-0">
        <div class="border rounded pa-4">
          <div class="text-h6 text-primary">{{ users }}</div>
          <div class="text-body-2">{{ $tr('admin', 'key_78') }}</div>
        </div>
      </v-col>
      <v-col md="3" sm="12" cols="12" class="py-0">
        <div class="border rounded pa-4">
          <div class="text-h6 text-primary">{{ apps }}</div>
          <div class="text-body-2">{{ $tr('admin', 'key_79') }}</div>
        </div>
      </v-col>
      <v-col md="3" sm="12" cols="12" class="py-0">
        <div class="border rounded pa-4">
          <div class="text-h6 text-primary">{{ paid_apps }}</div>
          <div class="text-body-2">{{ $tr('admin', 'key_80') }}</div>
        </div>
      </v-col>
      <v-col md="3" sm="12" cols="12" class="py-0">
        <div class="border rounded pa-4">
          <div class="text-h6 text-primary">{{ builds }}</div>
          <div class="text-body-2">{{ $tr('admin', 'key_81') }}</div>
        </div>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="mt-4">
        <div class="border rounded">
          <v-toolbar color="transparent">
            <v-toolbar-title> {{ $tr('admin', 'key_85') }}</v-toolbar-title>
            <v-spacer/>
            <v-btn-toggle
              density="compact"
              rounded
              class="mr-4"
              variant="outlined"
            >
              <v-btn height="30" :active="statMode === 'last_month'" @click="statMode = 'last_month'">
                {{ $tr('admin', 'key_86') }}
              </v-btn>
              <v-btn height="30" :active="statMode === 'last_year'"  @click="statMode = 'last_year'">
                {{ $tr('admin', 'key_87') }}
              </v-btn>
            </v-btn-toggle>
          </v-toolbar>
          <v-divider/>
          <v-row>
            <v-col md="12" sm="12" cols="12" class="py-0">
              <div class="pa-4 mb-4" style="height: 400px">
                <PageLoading v-if="chartLoading"/>
                <Line
                  v-else
                  :key="`chart_${$store.state.theme}`"
                  :data="handleChartData"
                  :options="handleChartOptions"
                />
              </div>
              <div class="d-flex justify-space-between align-center pa-5 mb-4">
                <div class="text-center">
                  <div class="text-h5 font-weight-medium text-primary">
                    {{ chartLoading ? '-' : `${amount} ${$store.state.config.currency}` }}
                  </div>
                  <div class="text-body-2">
                    {{ $tr('admin', 'key_82') }}
                  </div>
                </div>
                <div class="text-center">
                  <div class="text-h5 font-weight-medium text-primary">
                    {{ chartLoading ? '-' : total }}
                  </div>
                  <div class="text-body-2">
                    {{ $tr('admin', 'key_83') }}
                  </div>
                </div>
                <div class="text-center">
                  <div class="text-h5 font-weight-medium text-primary">
                    {{ chartLoading ? '-' : builds_total }}
                  </div>
                  <div class="text-body-2">
                    {{ $tr('admin', 'key_84') }}
                  </div>
                </div>
              </div>
            </v-col>
          </v-row>
        </div>
      </v-col>
      <v-col md="12" sm="12" cols="12" class="mt-4 mb-5">
        <div class="border rounded">
          <v-toolbar color="transparent">
            <v-toolbar-title> {{ $tr('admin', 'key_142') }}</v-toolbar-title>
          </v-toolbar>
          <v-divider/>
          <v-list
            item-props
            density="comfortable"
          >
            <template v-if="pending_apps.length">
              <div v-for="(app) in pending_apps">
                <v-list-item
                  lines="two"
                  density="default"
                  :to="`/admin/apps/${app.uid}/main`"
                >
                  <v-list-item-title>{{ app.name }}</v-list-item-title>
                  <v-list-item-subtitle>UID: {{ app.uid }}</v-list-item-subtitle>
                  <template v-slot:append>
                    <v-list-item-action>
                      <v-icon icon="mdi-chevron-right"/>
                    </v-list-item-action>
                  </template>
                </v-list-item>
              </div>
              <v-divider/>
            </template>
            <template v-else>
              <v-list-item
                lines="two"
                density="default"
              >
                <v-list-item-title>{{ $tr('admin', 'key_143') }}</v-list-item-title>
              </v-list-item>
            </template>
          </v-list>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import PageBar from "@/components/blocks/PageBar.vue";
import PageLoading from "@/components/blocks/PageLoading.vue";
import dashboardService from "@/services/dashboard/dashboard.service";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

export default {
  name: 'Dashboard',
  components: {PageLoading, PageBar, Line},
  data() {
    let self = this;
    return {
      loading: true,
      chartLoading: true,
      amount: 0,
      total: 0,
      users: 0,
      apps: 0,
      builds_total: 0,
      paid_apps: 0,
      builds: 0,
      pending_apps: [],
      notifications: [],
      statMode: "last_month",
      chartData: {
        labels: [],
        datasets: [
          {
            label: '',
            backgroundColor: '#184BFB',
            data: [],
            borderColor: "#184BFB",
            borderJoinStyle: 'round',
            borderWidth: 3,
          }
        ]
      },
      chartOptions: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            grid: {
              color: '#CFD8DC'
            }
          },
          x: {
            grid: {
              color: '#CFD8DC'
            }
          }
        },
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return self.getTooltipContext(context);
              }
            }
          }
        }
      },
    }
  },
  computed: {
    handleChartData:function () {
      let item = this.chartData;
      return item;
    },
    handleChartOptions:function () {
      let item = this.chartOptions;

      const color = this.$store.state.theme === "light" ? "#CFD8DC" : "#455A64";

      item.scales.y.grid.color = color;
      item.scales.x.grid.color = color;
      return item;
    }
  },
  watch: {
    statMode: function () {
      this.getCounts();
    }
  },
  methods: {
    getTooltipContext(context) {
      let label = context.dataset.label || '';
      if (context.parsed.y !== null) {
        label += `${context.parsed.y.toFixed(2)} ${this.$store.state.config.currency}`;
      }
      return label;
    },
    getCounts() {
      this.chartLoading = true;
      dashboardService.getTotalData().then((res) => {
        const data = res.data;
        this.users = data.users;
        this.apps = data.apps;
        this.paid_apps = data.paid_apps;
        this.builds = data.builds;
        this.pending_apps = data.pending_apps;
        this.notifications = data.notifications;
        this.loading = false;
        this.getChart();
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      });
    },
    getChart() {
      dashboardService.getChart(this.statMode).then((res) => {
        const data = res.data;
        this.chartData.labels = data.labels;
        this.chartData.datasets[0].data = Object.keys(data.data).map((key) => data.data[key]);
        this.amount = data.amount;
        this.total = data.total;
        this.builds_total = data.builds;
        this.chartLoading = false;
      }).catch(e => {
        this.$store.commit('openSnackbar', e);
      })
    }
  },
  beforeMount() {
    this.getCounts();
  }
};
</script>
