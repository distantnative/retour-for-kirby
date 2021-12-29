<template>
  <k-view class="retour-plugin-tab">
    <header>
      <h3><k-icon type="road-sign" /> Retour for Kirby</h3>
    </header>

    <ul class="k-system-info-box" style="--columns: 4">
      <li>
        <dl>
          <dt>
            {{ $t("retour.system.redirects") }}
          </dt>
          <dd>{{ data.redirects }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t("retour.system.failures") }}
          </dt>
          <dd>{{ data.failures }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t("retour.system.deleteAfter") }}
          </dt>
          <dd>
            {{
              $t("retour.system.deleteAfter.months", {
                count: data.deleteAfter,
              })
            }}
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t("retour.system.support") }}
          </dt>
          <dd>
            <k-button
              link="https://paypal.me/distantnative"
              target="_blank"
              theme="positive"
            >
              💛 {{ $t("retour.system.support.donate") }}
            </k-button>
          </dd>
        </dl>
      </li>
    </ul>

    <footer class="mt-2">
      <!-- eslint-disable vue/no-v-html -->
      <k-text
        theme="help"
        v-html="
          $t('retour.system.docs', {
            docs: 'https://distantnative.com/retour/docs',
          })
        "
      />
      <!-- eslint-enable vue/no-v-html -->
    </footer>
  </k-view>
</template>

<script>
export default {
  props: {
    data: Object,
  },
};
</script>

<style>
.retour-plugin-tab header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}
.retour-plugin-tab h3 {
  display: flex;
  align-items: center;
  font-weight: bold;
}
.retour-plugin-tab h3 .k-icon {
  margin-right: 0.75rem;
}
.retour-plugin-tab .k-system-info-box {
  margin-bottom: 0.75rem;
}
</style>
