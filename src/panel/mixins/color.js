export default {
  computed: {
    color() {
      if (!this.value) {
        return "var(--color-border)";
      }

      if (parseInt(this.value) >= 400) {
        return "var(--color-negative-light)";
      }

      if (parseInt(this.value) >= 300) {
        return "var(--color-positive-light)";
      }

      return "var(--color-focus-light)";
    },
  },
};
