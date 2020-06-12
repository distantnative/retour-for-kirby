<template>
  <div class="retour-settings-tab">

    <ul class="k-system-info-box bg-white p-3 flex items-center shadow rounded-sm">
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t("retour.settings.redirected") }}</dt>
          <dd>{{ redirected }}</dd>
        </dl>
      </li>
      <li>
        <dl class="text-sm text-gray mb-2">
          <dt>{{ $t("retour.settings.failed") }}</dt>
          <dd>{{ failed }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t("retour.settings.deleteAfter") }}</dt>
          <dd>{{ $t("retour.settings.deleteAfter.months", { count: $store.state.retour.system.deleteAfter || '–' }) }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t('retour.settings.support') }}</dt>
          <dd>
            <k-button
              link="https://paypal.me/distantnative"
              target="_blank"
              theme="positive"
            >{{ $t('retour.settings.support.donate') }} 💛</k-button>
          </dd>
        </dl>
      </li>
    </ul>

    <footer class="k-field-footer">
      <div data-theme="help" class="k-text k-field-help">
        <span
          v-html="$t('retour.settings.docs', { docs: 'https://distantnative.com/retour/docs' })"
        />
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  computed: {
    failed() {
      return this.$store.state.retour.data.failures.reduce((i, x) => {
        return i += parseInt(x.hits)
      }, 0);
    },
    redirected() {
      return this.$store.state.retour.data.routes.reduce((i, x) => {
        return i += parseInt(x.hits);
      }, 0);
    }
  }
}
</script>
