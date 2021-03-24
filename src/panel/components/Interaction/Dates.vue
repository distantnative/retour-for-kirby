<template>
  <k-dropdown class="dates">
    <k-button-group>
      <k-button
        icon="calendar"
        @click="$refs.calendar.open()"
      >
        {{ label }}
      </k-button>
      <k-dropdown-content ref="calendar">
        <k-calendar
          :multiple="true"
          :value="value"
          @input="onInput"
        />
      </k-dropdown-content>
    </k-button-group>
  </k-dropdown>
</template>

<script>
export default {
  computed: {
    label() {
      const unit = this.$store.getters["retour/unit"];
      const from = this.$store.state.retour.view.from;
      const to   = this.$store.state.retour.view.to;

      if (unit === "day") {
        return `${from.format("D")} ${this.month(from)} ${from.format("YYYY")}`;
      }

      if (unit === "month") {
        return `${this.month(from)} ${from.format("YYYY")}`;
      }

      if (unit === "year") {
        return `${from.format("YYYY")}`;
      }

      // within same month
      if (from.isSame(to, "month")) {
        return `${from.format("D")} - ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
      }

      // within same year
      if (from.isSame(to, "year")) {
        return `${from.format("D")} ${this.month(from)} - ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
      }

      return `${from.format("D")} ${this.month(from)} ${from.format("YYYY")} - ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
    },
    value() {
      return Object.values(this.$store.state.retour.view).map(date => date.format('YYYY-MM-DD HH:mm:ss'));
    }
  },
  methods: {
    month(date) {
      date = date.format("MMMM");
      date = this.$helper.string.lcfirst(date);
      return this.$t("months." + date);
    },
    onInput(values) {
      if (values.length === 2) {
        this.$store.dispatch("retour/view", {
          from: this.$library.dayjs(values[0]),
          to:   this.$library.dayjs(values[1])
        });
        this.$refs.calendar.close();
      }
    }
  }
};
</script>

<style>
.retour .dates  {
  display: inline-block;
}
.retour .dates .k-dropdown-content {
  margin: 0;
}
</style>