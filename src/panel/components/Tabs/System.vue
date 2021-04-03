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
          <dd>{{ redirects }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.system.failures') }}
          </dt>
          <dd>{{ fails }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.system.deleteAfter') }}
          </dt>
          <dd>
            {{ $t("retour.system.deleteAfter.months", { count: $store.state.retour.meta.deleteAfter || '–' }) }}
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
    data() {
      return this.$store.state.retour.data;
    },
    fails() {
      return this.reduce(this.data.fails);
    },
    redirects() {
      return this.reduce(this.data.redirects);
    }
  },
  methods: {
    reduce: data => data.reduce((i, x) => i += parseInt(x.hits || 0), 0)
  }
}
</script>

<style>
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
  margin-right: .75rem;
}
.rt-settings-tab .k-system-info-box {
  margin-bottom: .75rem;
}
</style>
