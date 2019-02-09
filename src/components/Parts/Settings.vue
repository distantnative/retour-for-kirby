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
              {{ $t('retour.settings.installed') }}
              <span class="hide">📍</span>
            </dt>
            <dd :data-negative="outdated">
              {{ options.version }}
            </dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>
              {{ $t('retour.settings.latest') }}
              <span class="hide">🕒</span>
            </dt>
            <dd>{{ latest }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('retour.settings.support') }} 💛</dt>
            <dd>
              <k-button link="https://buymeacoff.ee/distantnative" target="_blank" theme="positive">
                {{ $t('retour.settings.support.juice') }}
              </k-button> &middot; <k-button link="https://paypal.me/distantnative" target="_blank" theme="positive">
                {{ $t('retour.settings.support.donate') }}
              </k-button> &middot;
              <k-button link="https://a.paddle.com/v2/click/1129/35921?link=1170" target="_blank" theme="positive">
                {{ $t('retour.settings.support.affiliate') }}
              </k-button>
            </dd>
          </dl>
        </li>
      </ul>

      <footer v-if="outdated" class="k-field-footer">
        <div data-theme="help" class="k-text k-field-help">
          Download <a href="https://github.com/distantnative/retour-for-kirby/archive/master.zip">latest version</a>.
        </div>
      </footer>
    </section>

    <br>

    <section class="k-system-info">
      <header class="k-field-header">
        <label class="k-field-label">
          {{ $t('retour.settings.overview') }}
        </label>

        <k-button-group>
          <k-button v-if="debug" icon="download" @click="samples">
            Load sample data
          </k-button>
          <k-button icon="trash" theme="negative" @click="flush">
            {{ $t('retour.settings.log.clear') }}
          </k-button>
        </k-button-group>
      </header>

      <ul class="k-system-info-box">
        <li>
          <dl>
            <dt>{{ $t('retour.settings.routes') }}</dt>
            <dd>{{ routes }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('retour.settings.fails') }}</dt>
            <dd>{{ failed }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('retour.settings.redirects') }}</dt>
            <dd>{{ redirected }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('retour.settings.options.limit') }}</dt>
            <dd>{{ options.limit }}</dd>
          </dl>
        </li>
      </ul>

      <footer class="k-field-footer">
        <div data-theme="help" class="k-text k-field-help">
          Learn more about options <a href="https://github.com/distantnative/retour-for-kirby">in the docs</a>.
        </div>
      </footer>
    </section>
  </div>
</template>

<script>
export default {
  props: {
    fails: Array,
    redirects: Array,
    options: Object
  },
  data() {
    return {
      latest: "..."
    }
  },
  computed: {
    debug() {
      return window.panel.debug;
    },
    failed() {
      return this.fails.reduce((a, b) => a += b.fails, 0);
    },
    outdated() {
      return this.latest != "..." && this.options.version !== this.latest;
    },
    redirected() {
      return this.fails.reduce((a, b) => a += b.redirects, 0);
    },
    routes() {
      return this.redirects.length;
    }
  },
  mounted() {
    this.fetch();
  },
  methods: {
    fetch() {
      const api = "https://api.github.com/repos/distantnative/retour-for-kirby/releases";
      fetch(api, { method: "GET" }).then(x => x.json()).then(response => {
        this.latest = response[0].name;
      });
    },
    flush() {
      this.$api.patch("retour/flush").then(() => {
        this.$emit("reload");
      });
    },
    samples() {
      this.$api.post("retour/samples").then(() => {
        this.$emit("reload");
      });
    }
  }
}
</script>

<style>
.k-retour-view [data-negative] {
  color: #c82829;
}
</style>

