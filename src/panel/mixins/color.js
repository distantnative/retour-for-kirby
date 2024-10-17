export default {
  computed: {
    color() {
      if (!this.value) {
        return "var(--color-gray-400)";
      }

      if (parseInt(this.value) >= 400) {
        return "var(--color-red-500)";
      }

      if (parseInt(this.value) >= 300) {
        return "var(--color-green-500)";
      }

      return "var(--color-blue-500)";
    },
  },
};
