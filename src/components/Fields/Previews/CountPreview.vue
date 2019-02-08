<template>
  <p class="k-structure-table-text k-retour-hits-field-preview">
    <abbr :title="'Last: ' + (value.last || '–')">
      <aside>
        <k-icon type="circle" class="back" />
        <k-icon type="circle" :style="'--factor:' + factor" />
      </aside>
      <div v-if="value.last">
        <span>{{ value.hits }}</span>
        <small>{{ $t('retour.redirects.hit.last') }}: {{ short }}</small>
      </div>
      <div v-else>
        –
      </div>
    </abbr>
  </p>
</template>

<script>
export default {
  props: {
    value: String,
    column: Object,
    field: Object
  },
  computed: {
    factor() {
      if (this.value.last) {
        const date   = new Date(this.value.last);
        const today  = new Date();
        const diff   = Math.abs(today.getTime() - date.getTime());
        const days   = Math.ceil(diff / (1000 * 3600 * 24));
        const factor = 1 - (days/30);
        return factor > 0 ? factor : 0;
      }

      return 0;
    },
    short() {
      let date  = new Date(this.value.last);
      let day   = date.getDate().toString().padStart(2, "0");
      let month = (date.getMonth() + 1).toString().padStart(2, "0");
      let year  = date.getFullYear().toString().substr(-2);
      return `${day}/${month}/${year}`;
    }
  }
}
</script>

<style lang="scss">
.k-retour-hits-field-preview {
  > abbr {
    position: relative;
    display: flex;

    &[title] {
      text-decoration: none;
    }

    > aside {
      width: 1.2em;
    }

    .k-icon {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(#4271ae, var(--factor));
    }

    .back {
      position: absolute;
      color: rgba(0,0,0,.1);
    }

    > div {
      margin-left: .5em;
      line-height: 1.2;
    }

    span {
      font-size: .9em;
    }

    small {
      display: block;
      font-size: .65em;
    }
  }
}
</style>
