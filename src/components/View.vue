<template>
  <k-view class="k-retour-view">
    <k-header>
      {{ $t('view.retour') }}

      <k-button-group slot="left">
        <k-button
          v-for="part in parts"
          :key="part.name"
          :icon="part.icon"
          :current="current === part.name"
          @click="go(part.name)"
        >
          {{ $t('rt.' + part.name) }}
        </k-button>
      </k-button-group>

      <k-button-group slot="right">
        <k-button
          :disabled="loading"
          :icon="loading ? 'loader' : 'refresh'"
          class="retour-loader"
          @click="fetch()"
        >
          {{ $t(loading ? 'loading' : 'rt.refresh') }}
        </k-button>
      </k-button-group>
    </k-header>

    <rt-dashboard
      v-show="current === 'dashboard'"
      :stats="stats"
      @navigate="fetchStats"
    />

    <rt-redirects
      ref="redirects"
      v-show="current === 'redirects'"
      :canUpdate="canUpdate"
      :options="options"
      :redirects="redirects"
      @update="fetchRedirects"
    />

    <rt-logs
      v-show="current === 'fails'"
      :canUpdate="canUpdate"
      :logs="logs"
      :options="options"
      @go="go(...$event)"
    />

    <rt-settings
      v-show="current === 'settings'"
      :canUpdate="canUpdate"
      :logs="logs"
      :options="options"
      :redirects="redirects"
      @reload="fetch"
    />
  </k-view>
</template>

<script>

import Dashboard from "./Parts/Dashboard.vue";
import Redirects from "./Parts/Redirects.vue";
import Logs     from "./Parts/Logs.vue";
import Settings  from "./Parts/Settings.vue";

export default {
  components: {
    "rt-dashboard": Dashboard,
    "rt-redirects": Redirects,
    "rt-logs":     Logs,
    "rt-settings" : Settings
  },
  data() {
    return {
      current: "dashboard",
      logs: [],
      redirects: [],
      stats: {
        data: null,
        frame: "month",
        offset: 0
      },
      options: {
        headers: {}
      }
    }
  },
  computed: {
    canAccess() {
      return !(this.$permissions.hasOwnProperty("access") &&
      this.$permissions.access.hasOwnProperty("retour") &&
      this.$permissions.access.retour === false);
    },
    canUpdate() {
      return !(this.$permissions.hasOwnProperty("site") &&
        this.$permissions.site.hasOwnProperty("update") &&
        this.$permissions.site.update === false);
    },
    loading() {
      return this.$store.state.isLoading;
    },
    parts() {
      return [
        { name: "dashboard", icon: "dashboard" },
        { name: "redirects", icon: "url" },
        { name: "fails", icon: "protected" },
        { name: "settings", icon: "cog" }
      ];
    }
  },
  created() {
    if (!this.canAccess) {
      this.$router.push("/");
    }
    this.fetch();
  },
  methods: {
    fetch() {
      this.$store.dispatch("isLoading", true);

      const process = this.process();
      const system  = this.fetchSystem();

      return Promise.all([process, system]).then(() => {
        const stats = this.fetchStats([
          this.stats.frame,
          this.stats.offset
        ]);
        const redirects = this.fetchRedirects();
        const logs      = this.fetchLogs();

        return Promise.all([stats, redirects, logs]).then(() => {
          this.$store.dispatch("isLoading", false);
        });
      });
    },
    async fetchLogs() {
      const response = await this.$api.get("retour/logs");
      this.logs = response;
    },
    async fetchRedirects() {
      const response = await this.$api.get("retour/redirects");
      this.redirects = response;
    },
    async fetchStats(stats) {
      const endpoint = "retour/stats/" + stats[0] + "/" + stats[1];
      const response = await this.$api.get(endpoint);
      this.stats = {
        data: response,
        frame: stats[0],
        offset: stats[1]
      };
    },
    async fetchSystem() {
      const response = await this.$api.get("retour/system");
      this.options = response;
    },
    go(part, after = () => {}) {
      this.current = part;
      this.$events.$emit("retour-go", part);
      after(this);

      // Currently not supported by Kirby Panel
      // window.location.hash = part;
    },
    async process() {
      await this.$api.post("retour/process");
    }
  }
}
</script>

<style>
.k-retour-view [aria-current="true"] {
  color: #4271ae;
}

.retour-loader[disabled] .k-icon > svg {
    transform: rotate(-180deg);
    animation: spin-loader 1.5s linear infinite;
}

@keyframes spin-loader { 100% { transform: rotate(180deg); } }

.k-retour-view .hide { opacity: 0; }
</style>

