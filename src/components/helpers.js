

export function date(array) {
  return array.map(x => {
    if (x.last) {
      x.last = x.last.replace(/-/g, "/");
    }
    return x;
  });
}

export function status(code) {
  if (code === "disabled") {
    return "no"
  }

  if (parseInt(code) >= 300 && parseInt(code) < 400) {
    return "yes";
  }

  return "mmm";
}

export const permissions = {
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

