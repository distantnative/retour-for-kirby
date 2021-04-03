
export const canAccess = (app) => {
  if (
    app.$permissions.hasOwnProperty("access") &&
    app.$permissions.access.hasOwnProperty("retour")
  ) {
    return app.$permissions.access.retour !== false;
  }

  return true;
};

export default {
  computed: {
    canAccess() {
      return canAccess(this);
    },
    canUpdate() {
      if (
        this.$permissions.hasOwnProperty("site") &&
        this.$permissions.access.hasOwnProperty("update")
      ) {
        return this.$permissions.site.update !== false;
      }

      return this.canAccess;
    }
  }
}
