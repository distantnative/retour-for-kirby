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
      <div v-else>–</div>
    </abbr>
  </p>
</template>

<script>

import dayjs from 'dayjs';

export default {
  props: {
    value: String,
    column: Object,
    field: Object
  },
  computed: {
    factor() {
      if (this.value.last) {
        let date   = dayjs(this.value.last);
        let today  = dayjs();
        let days   = today.diff(date, 'day');
        let factor = 1 - (days/30);
        return factor > 0 ? factor : 0;
      }

      return 0;
    },
    short() {
      return dayjs(this.value.last).format('DD/MM/YY');
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
