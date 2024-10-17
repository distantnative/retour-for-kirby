export default {
  computed: {
    color() {
      const value = this.value || this.default;

      if (!value) {
        return "var(--color-gray-400)";
      }

      if (parseInt(value) >= 400) {
        return "var(--color-red-500)";
      }

      if (parseInt(value) >= 300) {
        return "var(--color-green-500)";
      }

      return "var(--color-blue-500)";
    },
  },
};
