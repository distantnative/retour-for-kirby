<template>
  <div class="rt-settings-tab">

    <header>
      <h3>
        <k-icon type="road-sign" /> Retour for Kirby
      </h3>
    </header>

    <ul class="k-system-info-box">
      <li>
        <dl>
          <dt>
            {{ $t('retour.system.redirects') }}
          </dt>
          <dd>{{ redirected }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.system.failures') }}
          </dt>
          <dd>{{ failed }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.system.deleteAfter') }}
          </dt>
          <dd>
            {{ $t("retour.system.deleteAfter.months", { count: $store.state.retour.system.deleteAfter || '–' }) }}
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.system.support') }}
          </dt>
          <dd>
            <k-button
              link="https://paypal.me/distantnative"
              target="_blank"
              theme="positive"
            >
              💛 {{ $t('retour.system.support.donate') }}
            </k-button>
          </dd>
        </dl>
      </li>
    </ul>

    <footer class="mt-2">
      <k-text
        theme="help"
        v-html="$t('retour.system.docs', {
          docs: 'https://distantnative.com/retour/docs'
        })"
      />
    </footer>
  </div>
</template>

<script>
export default {
  computed: {
    failed() {
      return this.reduce(this.$store.state.retour.data.failures);
    },
    redirected() {
      return this.reduce(this.$store.state.retour.data.routes);
    },
    updateStyle() {
      if (this.$store.state.retour.system.update < 0) {
        return "color: #c82829; font-weight: bold;";
      }
    }
  },
  methods: {
    reduce: data => data.reduce((i, x) => i += parseInt(x.hits || 0), 0)
  }
}
</script>

<style lang="scss">
.rt-settings-tab header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: .75rem;
}

.rt-settings-tab h3 {
  display: flex;
  align-items: center;
  font-weight: bold;
}

.rt-settings-tab h3 .k-icon {
  margin-right: .5rem;
}

.rt-settings-tab .k-system-info-box {
  margin-bottom: .75rem;
}
</style>
