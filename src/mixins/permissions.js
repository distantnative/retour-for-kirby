
export default {
  computed: {
    canAccess() {
      return !(this.$permissions.hasOwnProperty("access") &&
        this.$permissions.access.hasOwnProperty("retour") &&
        this.$permissions.access.retour === false);
    },
    canUpdate() {
      return !(this.$permissions.hasOwnProperty("site") &&
        this.$permissions.site.hasOwnProperty("update") &&
        this.$permissions.site.update === false);
    }
  }
}
