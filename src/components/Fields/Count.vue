<script>
export default {
  extends: "k-info-field",
  props: {
    value: Object
  },
  computed: {
    text() {
      if (!this.value) {
        return "-";
      }

      return `<b>${this.value.hits || 0} ${this.$t("rt.redirects.hits")}</b> (${this.$t("rt.redirects.hit.last")}: ${this.value.last || "–"})`;
    }
  }
}
</script>
