
export default {
  computed: {
    color() {
      if (!this.value) {
        return "gray-light"
      }

      if (parseInt(this.value) >= 300 && parseInt(this.value) < 400) {
        return "green-light";
      }

      return "blue-light";
    }
  }
}
