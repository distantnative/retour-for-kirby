<template>
  <k-view class="rt-settings">

    <ul class="k-system-info-box">
      <li>
        <dl>
          <dt>{{ $t("retour.settings.redirects") }}</dt>
          <dd>{{ redirected }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>{{ $t("retour.settings.fails") }}</dt>
          <dd>{{ failed }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>{{ $t("retour.settings.deleteAfter") }}</dt>
          <dd>{{ $t("retour.settings.deleteAfter.months", { count: $store.state.retour.options.deleteAfter || '–' }) }}</dd>
        </dl>
      </li>
      <li>
        <dl>
          <dt>{{ $t('retour.settings.support') }}</dt>
          <dd>
            <k-button
              link="https://paypal.me/distantnative"
              target="_blank"
              theme="positive"
            >
              {{ $t('retour.settings.supporetour.donate') }} 💛
            </k-button>
          </dd>
        </dl>
      </li>
    </ul>

    <footer class="k-field-footer">
      <div data-theme="help" class="k-text k-field-help">
        <span v-html="$t('retour.settings.docs', { url: 'https://github.com/distantnative/retour-for-kirby' })" />
      </div>
      <k-button-group>
        <k-button
          :disabled="!canUpdate"
          icon="trash"
          theme="negative"
          @click="$refs.dialog.open()"
        >
          {{ $t('retour.settings.log.clear') }}
        </k-button>
      </k-button-group>
    </footer>

    <k-dialog
      ref="dialog"
      :button="$t('retour.settings.log.clear')"
      theme="negative"
      icon="trash"
      @submit="flush"
    >
      <k-text>
        {{ $t('retour.settings.log.clear.confirm') }}
      </k-text>
    </k-dialog>
  </k-view>
</template>

<script>
import {permissions} from "../helpers.js";

export default {
  mixins: [permissions],
  computed: {
    data() {
       return this.$store.state.retour.data;
    },
    failed() {
      return this.data.fails.reduce((i, x) => i += parseInt(x.hits), 0);

    },
    redirected() {
      return this.data.redirects.reduce((i, x) => i += parseInt(x.hits), 0);
    },
    routes() {
      return this.data.redirects.length;
    }
  },
  methods: {
    flush() {
      this.$api.post("retour/flush").then(() => {
        this.$refs.dialog.close();
        this.$store.dispatch("retour/load");
      });
    }
  }
}
</script>

<style lang="scss">
.rt-settings {
  .k-field-footer {
    display: flex;
    justify-content: space-between;

    .k-button-group {
      margin-top: -.25rem;
      transform: scale(.9);
      transform-origin: top right;
    }
  }

  .k-system-info-box {
    .k-button {
      font-size: inherit;
    }
  }

  @media screen and (max-width: 45em) {
    ul {
      display: block;
    }

    li:not(:last-child) {
      margin-bottom: 1rem;
    }
  }
}
</style>
