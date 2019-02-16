<template>
  <div>
    <section class="k-system-info">
      <header class="k-field-header">
        <label class="k-field-label">
          {{ $t('rt.settings.overview') }}
        </label>

        <k-button-group>
          <k-button
            v-if="options.debug"
            :disabled="!canUpdate"
            icon="download"
            @click="samples"
          >
            Load sample data
          </k-button>
          <k-button
            :disabled="!canUpdate"
            icon="trash"
            theme="negative"
            @click="$refs.dialog.open()"
          >
            {{ $t('rt.settings.log.clear') }}
          </k-button>
        </k-button-group>
      </header>

      <k-dialog
        ref="dialog"
        :button="$t('rt.settings.log.clear')"
        theme="negative"
        icon="trash"
        @submit="flush"
      >
        <k-text>
          {{ $t('rt.settings.log.clear.confirm') }}
        </k-text>
      </k-dialog>

      <ul class="k-system-info-box">
        <li>
          <dl>
            <dt>{{ $t('rt.settings.routes') }}</dt>
            <dd>{{ routes }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('rt.settings.fails') }}</dt>
            <dd>{{ failed }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('rt.settings.redirects') }}</dt>
            <dd>{{ redirected }}</dd>
          </dl>
        </li>
      </ul>

      <footer class="k-field-footer">
        <div data-theme="help" class="k-text k-field-help" v-html="$t('rt.settings.docs', { url: 'https://github.com/distantnative/retour-for-kirby' })" />
      </footer>
    </section>

    <br><br>

    <section class="k-system-info">
      <header class="k-field-header">
        <label class="k-field-label">
          {{ $t('rt.settings.headline') }}
        </label>
      </header>

      <ul class="k-system-info-box">
        <li>
          <dl>
            <dt>
              {{ $t('rt.settings.installed') }}
              <span class="hide">📍</span>
            </dt>
            <dd :data-outdated="outdated">
              {{ options.version }}
            </dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>
              {{ $t('rt.settings.latest') }}
              <span class="hide">🕒</span>
            </dt>
            <dd>{{ latest }}</dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('rt.settings.support') }} 💛</dt>
            <dd>
              <k-button link="https://buymeacoff.ee/distantnative" target="_blank" theme="positive">
                {{ $t('rt.settings.support.juice') }}
              </k-button> &middot; <k-button link="https://paypal.me/distantnative" target="_blank" theme="positive">
                {{ $t('rt.settings.support.donate') }}
              </k-button> &middot;
              <k-button link="https://a.paddle.com/v2/click/1129/35921?link=1170" target="_blank" theme="positive">
                {{ $t('rt.settings.support.affiliate') }}
              </k-button>
            </dd>
          </dl>
        </li>
      </ul>

      <footer v-if="outdated" class="k-field-footer">
        <div data-theme="help" class="k-text k-field-help" v-html="$t('rt.settings.download', { url: 'https://github.com/distantnative/retour-for-kirby/archive/master.zip' })" />
      </footer>
    </section>
  </div>
</template>

<script>
export default {
  props: {
    canUpdate: Boolean,
    logs: Array,
    redirects: Array,
    options: Object
  },
  data() {
    return {
      latest: "..."
    }
  },
  computed: {
    failed() {
      return this.logs.reduce((a, b) => a += b.failed || 0, 0);
    },
    outdated() {
      return this.latest != "..." && this.options.version !== this.latest;
    },
    redirected() {
      return this.redirects.reduce((a, b) => a += b.hits || 0, 0);
    },
    routes() {
      return this.redirects.length;
    }
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$store.dispatch("isLoading", true);
      const api = "https://api.github.com/repos/distantnative/retour-for-kirby/releases";
      fetch(api, { method: "GET" }).then(x => x.json()).then(response => {
        this.latest = response[0].name;
        this.$store.dispatch("isLoading", false);
      });
    },
    flush() {
      this.$api.patch("retour/flush").then(() => {
        this.$refs.dialog.close();
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
.k-retour-view [data-outdated] {
  color: #c82829;
}

.k-retour-view .k-system-info .k-field-label {
  padding: 1rem 0;
  line-height: 1rem;
}
</style>
