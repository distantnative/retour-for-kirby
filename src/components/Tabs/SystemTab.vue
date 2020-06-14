<template>
  <div class="retour-settings-tab">

    <header class="flex items-center justify-between mb-3">
      <h3 class="flex items-center font-bold">
        <k-icon type="road-sign" class="mr-2" /> Retour for Kirby
      </h3>
      <k-button
        :text="$t('retour.system.update')"
        icon="refresh"
        @click="onUpdate"
      />
    </header>

    <ul class="k-system-info-box bg-white p-3 flex items-center shadow rounded-sm">
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t("retour.system.redirects") }}
          </dt>
          <dd>{{ redirected }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t("retour.system.failures") }}
          </dt>
          <dd>{{ failed }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t("retour.system.deleteAfter") }}
          </dt>
          <dd>
            {{ $t("retour.system.deleteAfter.months", { count: $store.state.retour.system.deleteAfter || '–' }) }}
          </dd>
        </dl>
      </li>
    </ul>

    <ul class="k-system-info-box bg-white p-3 flex items-center shadow rounded-sm mt-3">
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t("retour.system.version") }}
          </dt>
          <dd :class="updateClass">
            {{ $store.state.retour.system.version || "–" }}
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t("retour.system.release") }}
          </dt>
          <dd>
            <k-button
              :text="$store.state.retour.system.release || '–'"
              link="https://github.com/distantnative/retour-for-kirby/releases"
              target="_blank"
            />
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">{{ $t('retour.system.support') }}</dt>
          <dd>
            <k-button
              :text="'💛 ' + $t('retour.system.support.donate')"
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
      return this.reduce(this.$store.state.retour.data.manual) +
             this.reduce(this.$store.state.retour.data.tracked);
    },
    updateClass() {
      if (this.$store.state.retour.system.update < 0) {
        return "text-red";
      }

      if (this.$store.state.retour.system.update > 0) {
        return "text-purple";
      }
    }
  },
  methods: {
    onUpdate() {
      this.$store.dispatch("retour/system", true);
    },
    reduce(data) {
      return data.reduce((i, x) => {
        return i += parseInt(x.hits || 0)
      }, 0);
    }
  }
}
</script>
