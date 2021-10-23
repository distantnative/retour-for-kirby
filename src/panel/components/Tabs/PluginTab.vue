<template>
  <k-view class="retour-plugin-tab">
    <header>
      <h3>
        <k-icon type="road-sign" /> Retour for Kirby
      </h3>
    </header>

    <ul class="k-system-info-box">
      <li>
        <dl>
          <dt>
            {{ $t('retour.plugin.redirects') }}
          </dt>
          <dd>{{ data.redirects }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.plugin.failures') }}
          </dt>
          <dd>{{ data.failures }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.plugin.deleteAfter') }}
          </dt>
          <dd>
            {{ $t("retour.plugin.deleteAfter.months", { count: data.deleteAfter }) }}
          </dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>
            {{ $t('retour.plugin.support') }}
          </dt>
          <dd>
            <k-button
              link="https://paypal.me/distantnative"
              target="_blank"
              theme="positive"
            >
              💛 {{ $t('retour.plugin.support.donate') }}
            </k-button>
          </dd>
        </dl>
      </li>
    </ul>

    <footer class="mt-2">
      <k-text
        theme="help"
        v-html="$t('retour.plugin.docs', {
          docs: 'https://distantnative.com/retour/docs'
        })"
      />
    </footer>
  </k-view>
</template>

<script>
export default {
  props: {
    data: Object
  }
};
</script>

<style>
.retour-plugin-tab header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: .75rem;
}
.retour-plugin-tab h3 {
  display: flex;
  align-items: center;
  font-weight: bold;
}
.retour-plugin-tab h3 .k-icon {
  margin-right: .75rem;
}
.retour-plugin-tab .k-system-info-box {
  margin-bottom: .75rem;
}
</style>
