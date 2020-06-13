<template>
  <div class="retour-settings-tab">

    <header class="flex items-center justify-between mb-3">
      <h3 class="flex items-center font-bold">
        <k-icon type="road-sign" class="mr-2" /> Retour for Kirby
      </h3>
      <k-button
        text="Check for update"
        icon="refresh"
        @click="onUpdate"
      />
    </header>

    <ul class="k-system-info-box bg-white p-3 flex items-center shadow rounded-sm">
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t("retour.settings.redirected") }}</dt>
          <dd>{{ redirected }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t("retour.settings.failed") }}</dt>
          <dd>{{ failed }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t("retour.settings.deleteAfter") }}</dt>
          <dd>{{ $t("retour.settings.deleteAfter.months", { count: $store.state.retour.system.deleteAfter || '–' }) }}</dd>
        </dl>
      </li>
    </ul>

    <ul class="k-system-info-box bg-white p-3 flex items-center shadow rounded-sm mt-1">
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            Installed version
          </dt>
          <dd :class="updateClass">
            {{ $store.state.retour.system.version || "–" }}
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            Current release
          </dt>
          <dd>
            {{ $store.state.retour.system.release || "–" }}
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t('retour.settings.support') }}</dt>
          <dd>
            <k-button
              :text="'💛 ' + $t('retour.settings.support.donate')"
              link="https://paypal.me/distantnative"
              target="_blank"
              theme="positive"
            />
          </dd>
        </dl>
      </li>
    </ul>

    <footer class="mt-2">
      <k-text
        theme="help"
        v-html="$t('retour.settings.docs', {
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
      return this.$store.state.retour.data.failures.reduce((i, x) => {
        return i += parseInt(x.hits)
      }, 0);
    },
    redirected() {
      return this.$store.state.retour.data.routes.reduce((i, x) => {
        return i += parseInt(x.hits);
      }, 0);
    },
    updateClass() {
      if (this.$store.state.retour.system.update < 0) {
        return "text-red";
      }

      if (this.$store.state.retour.system.update > 0) {
        return "text-purple";
      }
    }
  }
}
</script>
