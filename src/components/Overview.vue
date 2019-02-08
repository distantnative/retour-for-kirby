<template>
  <k-view class="k-retour-view">
    <k-header>
      {{ $t('view.retour') }}

      <k-button-group slot="left">
        <k-button
          v-for="view in views"
          :key="view.name"
          :icon="view.icon"
          :current="current === view.name"
          @click="go(view.name)"
        >
          {{ $t('retour.' + view.name)}}
        </k-button>
      </k-button-group>

      <k-button-group slot="right">
        <k-icon v-if="loading" type="loader" class="retour-loader" />
      </k-button-group>
    </k-header>

    <template v-for="view in views">
      <component
        v-if="current === view.name"
        :key="view.name"
        :ref="view.name"
        :is="view.name"
        :options="options"
      />
    </template>

  </k-view>
</template>

<script>

import Dashboard from './Views/Dashboard.vue';
import Redirects from './Views/Redirects.vue';
import Fails     from './Views/Fails.vue';
import Settings  from './Views/Settings.vue';

export default {
  components: {
    dashboard: Dashboard,
    redirects: Redirects,
    fails:     Fails,
    settings : Settings
  },
  data() {
    return {
      current: null,
      options: {
        site   : null,
        view   : null
      },
      loading: false
    }
  },
  computed: {
    views() {
      return [
        {
          name: 'dashboard',
          icon: 'dashboard'
        },
        {
          name: 'redirects',
          icon: 'url'
        },
        {
          name: 'fails',
          icon: 'protected'
        },
        {
          name: 'settings',
          icon: 'cog'
        }
      ];
    }
  },
  created() {
    this.$events.$on('retour-load', this.load);
    this.$events.$on('retour-loaded', this.loaded);
    this.fetch();
  },
  destroyed() {
    this.$events.$off('retour-load', this.load);
    this.$events.$off('retour-loaded', this.loaded);
  },
  methods: {
    fetch() {
      this.$events.$emit('retour-load');
      this.$api.get('retour/system').then(response => {
        this.options = response;

        this.tmp().then(() => {
          this.current = this.options.view;
          this.$events.$emit('retour-loaded');
        });
      });
    },
    tmp() {
      return this.$api.get('retour/load')
    },
    go(view) {
      this.$events.$emit('retour-load');
      this.tmp().then(() => {
        this.current = view;
        this.$events.$emit('retour-loaded');
      });
    },
    load() {
      this.loading = true;
    },
    loaded() {
      this.loading = false;
    }
  }
}
</script>

<style lang="scss">


.k-retour-view {

  .k-header .k-headline {
    display: flex;

    > .k-icon {
      padding-right: .5em;
    }
  }

  [aria-current]:not([aria-current="false"]) {
    color: #4271ae;
  }
}

.retour-loader {
  display: block;
  padding: 0 .75rem;

  > svg {
    transform: rotate(-180deg);
    animation: spin 1.5s linear infinite;

    @keyframes spin {
      100% { transform: rotate(180deg); }
    }
  }
}

</style>

