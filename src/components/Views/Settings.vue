<template>
  <div>
    <section class="k-system-info">
      <header class="k-field-header">
        <label class="k-field-label">
          {{ options.description }}
        </label>
      </header>

      <ul class="k-system-info-box">
        <li>
          <dl>
            <dt>
              Installed plugin version
              <span style="opacity: 0;">
                🔜
              </span>
            </dt>
            <dd :data-negative="outdated">
              {{ options.version }}
            </dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>
              Latest plugin version
              <span style="opacity: 0;">
                ⚡️
              </span>
            </dt>
            <dd>{{ latest }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>Support development 💛</dt>
            <dd>
              <k-button link="https://buymeacoff.ee/distantnative" target="_blank" theme="positive">
                Buy a juice
              </k-button> &middot; <k-button link="https://paypal.me/distantnative" target="_blank" theme="positive">
                Donate
              </k-button> &middot;
              <k-button link="https://a.paddle.com/v2/click/1129/35921?link=1170" target="_blank" theme="positive">
                Buy a Kirby license
              </k-button>
            </dd>
          </dl>
        </li>
      </ul>

      <footer v-if="outdated" class="k-field-footer">
        <div data-theme="help" class="k-text k-field-help">
          Download <a href="https://github.com/distantnative/retour-for-kirby/archive/master.zip">
            latest version
          </a>.
        </div>
      </footer>
    </section>

    <br>

    <section class="k-system-info">
      <header class="k-field-header">
        <label class="k-field-label">
          Overview
        </label>

        <k-button-group>
          <k-button v-if="debug" icon="download" @click="samples">
            Load sample data
          </k-button>
          <k-button icon="trash" theme="negative" @click="flush">
            Clear log
          </k-button>
        </k-button-group>
      </header>

      <ul class="k-system-info-box">
        <li>
          <dl>
            <dt>Redirect routes</dt>
            <dd>{{ routes }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>Logged {{ $t('retour.fails') }}</dt>
            <dd>{{ fails }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>Successfully redirected</dt>
            <dd>{{ redirects }}</dd>
          </dl>
        </li>
      </ul>
      <ul class="k-system-info-box">
        <li>
          <dl>
            <dt>Default view</dt>
            <dd>
              dashboard
            </dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>List # items per page</dt>
            <dd>{{ options.limit }}</dd>
          </dl>
        </li>
        <li>
          <dl v-if="false">
            <dt>Keep log for</dt>
            <dd>
              6 months
            </dd>
          </dl>
        </li>
      </ul>

      <footer class="k-field-footer">
        <div data-theme="help" class="k-text k-field-help">
          Learn more about options <a href="https://github.com/distantnative/retour-for-kirby">
            in the docs
          </a>.
        </div>
      </footer>
    </section>
  </div>
</template>

<script>
export default {
  props: {
    options: Object
  },
  data() {
    return {
      routes: "...",
      fails: "...",
      redirects: "...",
      latest: "..."
    }
  },
  computed: {
    debug() {
      return window.panel.debug;
    },
    outdated() {
      return this.latest != "..." && this.options.version !== this.latest;
    }
  },
  mounted() {
    this.fetch();
  },
  methods: {
    count(response) {
      if (response.length > 0) {
        this.fails = response.reduce((a, b) => ({fails: a.fails + b.fails})).fails;
        this.redirects = response.reduce((a, b) => ({redirects: a.redirects + b.redirects})).redirects;
      } else {
        this.fails = 0;
        this.redirects = 0;
      }
    },
    fetch() {
      this.$events.$emit("retour-load");
      this.$api.get("retour/redirects").then(response => {
        this.routes = response.length;

        this.$api.get("retour/fails/fails").then(response => {
          this.count(response);

          fetch("https://api.github.com/repos/distantnative/retour-for-kirby/releases", { method: "GET" }).then(response => response.json()).then(response => {
            this.latest = response[0].name;
            this.$events.$emit("retour-loaded");
          });
        });
      });
    },
    flush() {
      this.$events.$emit("retour-load");
      this.$api.patch("retour/clear").then(() => {
        this.fetch();
      });
    },
    samples() {
      this.$events.$emit("retour-load");
      this.$api.post("retour/samples").then(() => {
        this.fetch();
      });
    }
  }
}
</script>

<style lang="scss">
.k-retour-view {
  [data-negative] {
    color: #c82829;
  }
}
</style>

