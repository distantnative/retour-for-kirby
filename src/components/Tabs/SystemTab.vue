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

    <k-auto-grid element="ul" style="--gap: 1.5rem" class="k-system-info-box bg-white p-3 shadow rounded-sm mb-3">
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t('retour.system.redirects') }}
          </dt>
          <dd class="truncate leading-tight">{{ redirected }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t('retour.system.failures') }}
          </dt>
          <dd class="truncate leading-tight">{{ failed }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t('retour.system.deleteAfter') }}
          </dt>
          <dd class="truncate leading-tight">
            {{ $t("retour.system.deleteAfter.months", { count: $store.state.retour.system.deleteAfter || '–' }) }}
          </dd>
        </dl>
      </li>
    </k-auto-grid>

    <k-auto-grid element="ul" style="--gap: 1.5rem" class="k-system-info-box bg-white p-3 shadow rounded-sm">
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t('retour.system.version') }}
          </dt>
          <dd :class="'truncate leading-tight ' + updateClass">
            {{ $store.state.retour.system.version || "–" }}
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt class="text-sm text-gray mb-2">
            {{ $t('retour.system.release') }}
          </dt>
          <dd class="truncate leading-tight">
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
          <dt class="text-sm text-gray mb-2">
            {{ $t('retour.system.support') }}
          </dt>
          <dd class="truncate leading-tight">
            <k-button
              :text="'💛 ' + $t('retour.system.support.donate')"
              link="https://paypal.me/distantnative"
              target="_blank"
              theme="positive"
            />
          </dd>
        </dl>
      </li>
    </k-auto-grid>

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
    updateClass() {
      if (this.$store.state.retour.system.update < 0) {
        return "text-red";
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
