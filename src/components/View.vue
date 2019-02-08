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
          @click="current = part.name"
        >
          {{ $t('retour.' + part.name) }}
        </k-button>
      </k-button-group>

      <k-button-group slot="right">
        <k-button
          :disabled="loading"
          :icon="loading ? 'loader' : 'refresh'"
          class="retour-loader"
          @click="fetch"
        >
          {{ $t(loading ? 'loading' : 'retour.refresh') }}
        </k-button>
      </k-button-group>
    </k-header>

    <template v-for="part in parts">
      <component
        :is="part.name"
        v-show="current === part.name"
        :key="part.name"
        :fails="fails"
        :redirects="redirects"
        :stats="stats"
        :options="options"
        @fails="fetchFails"
        @stats="fetchStats"
        @reload="fetch"
      />
    </template>
  </k-view>
</template>

<script>

import Dashboard from "./Parts/Dashboard.vue";
import Redirects from "./Parts/Redirects.vue";
import Fails     from "./Parts/Fails.vue";
import Settings  from "./Parts/Settings.vue";

export default {
  components: {
    dashboard: Dashboard,
    redirects: Redirects,
    fails:     Fails,
    settings : Settings
  },
  data() {
    return {
      current: "dashboard",
      fails: [],
      redirects: [],
      stats: {
        data: null,
        frame: "month",
        offset: 0
      },
      options: {
        site:  null,
        entry: null,
        headers: {}
      }
    }
  },
  computed: {
    loading() {
      return this.$store.state.isLoading;
    },
    parts() {
      return [
        {
          name: "dashboard",
          icon: "dashboard"
        },
        {
          name: "redirects",
          icon: "url"
        },
        {
          name: "fails",
          icon: "protected"
        },
        {
          name: "settings",
          icon: "cog"
        }
      ];
    }
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      return this.loadTemporaries().then(() => {
        const system    = this.fetchSystem();
        const fails     = this.fetchFails();
        const redirects = this.fetchRedirects();
        const stats     = this.fetchStats([
          this.stats.frame,
          this.stats.offset
        ]);

        Promise.all([system, stats, fails, redirects]);
      });
    },
    fetchFails(sort = "fails") {
      const endpoint = "retour/fails/" + sort;
      return this.$api.get(endpoint).then(response => {
        this.fails = response;
      });
    },
    fetchRedirects() {
      const endpoint = "retour/redirects";
      return this.$api.get(endpoint).then(response => {
        this.redirects = response;
      });
    },
    fetchStats(stats) {
      const endpoint = "retour/stats/" + stats[0] + "/" + stats[1];
      return this.$api.get(endpoint).then(response => {
        this.stats = {
          data: response,
          frame: stats[0],
          offset: stats[1]
        };
      });
    },
    fetchSystem() {
      const endpoint = "retour/system";
      return this.$api.get(endpoint).then(response => {
        this.options = response;
      });
    },
    loadTemporaries() {
      return this.$api.get("retour/load")
    }
  }
}
</script>

<style>
.k-retour-view .k-header .k-headline {
  display: flex;
}
.k-retour-view .k-header .k-headline > .k-icon {
  padding-right: .5em;
}

.k-retour-view [aria-current]:not([aria-current="false"]) {
  color: #4271ae;
}

.retour-loader[disabled] .k-icon > svg {
    transform: rotate(-180deg);
    animation: spin 1.5s linear infinite;
}
@keyframes spin {
  100% { transform: rotate(180deg); }
}
</style>

