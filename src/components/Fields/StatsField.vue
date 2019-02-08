
<script>
export default {
  extends: 'k-info-field',
  props: {
    value: Object
  },
  computed: {
    text() {
      if (this.value) {
        return `<b>${this.value.hits || 0} ${this.$t('retour.redirects.hits')}</b> (${this.$t('retour.redirects.hit.last')}: ${this.value.last || '–'})`;
      }

      return '–';
    }
  }
}
</script>
