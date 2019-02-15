<template>
  <p class="k-structure-table-text">
    <abbr class="rt-count-lb" :title="'Last: ' + (value.last || '–')">
      <aside>
        <k-icon type="circle" class="bg" />
        <k-icon type="circle" :style="'--f:' + factor" />
      </aside>
      <div v-if="value.last">
        <span>{{ value.hits }}</span>
        <small>{{ $t('rt.redirects.hit.last') }}: {{ short }}</small>
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
    date() {
      return new Date(this.value.last);
    },
    factor() {
      if (!this.value.last) {
        return 0;
      }

      const diff   = Math.abs((new Date()).getTime() - this.date.getTime());
      const days   = Math.ceil(diff / (1000 * 3600 * 24));
      const factor = 1 - (days/30);
      return factor > 0 ? factor : 0;
    },
    short() {
      let day   = this.date.getDate().toString().padStart(2, "0");
      let month = (this.date.getMonth() + 1).toString().padStart(2, "0");
      let year  = this.date.getFullYear().toString().substr(-2);
      return `${day}/${month}/${year}`;
    }
  }
}
</script>

<style lang="scss">
.rt-count-lb {
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
    color: rgba(#4271ae, var(--f));
  }

  .bg {
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
</style>
