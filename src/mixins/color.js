
export default {
  computed: {
    color() {
      if (!this.value) {
        return "gray-light";
      }

      if (parseInt(this.value) >= 400) {
        return "red-light";
      }

      if (parseInt(this.value) >= 300) {
        return "green-light";
      }

      return "blue-light";
    }
  }
}
