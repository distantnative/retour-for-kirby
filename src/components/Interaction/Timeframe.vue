<template>
  <k-dropdown class="retour-timeframe">
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
    dates() {
      return this.$store.state.retour.view.dates;
    },
    label() {
      const from = this.dates[0];
      const to   = this.dates[1];

      // single day
      if (from.isSame(to, "date")) {
        return `${from.format("D")} ${this.month(from)} ${from.format("YYYY")}`;
      }

      // full month
      if (
        from.isSame(to, "month") &&
        from.date() === 1 &&
        to.date() === to.daysInMonth()
      ) {
        return `${this.month(from)} ${from.format("YYYY")}`;
      }

      // full year
      if (
        from.isSame(to, "year") &&
        from.date() === 1 &&
        from.month() === 0 &&
        to.date() === 31 &&
        to.month() === 11
      ) {
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

      // custom
      return `${from.format("D")} ${this.month(from)} ${from.format("YYYY")} - ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
    },
    value() {
      return this.dates.map(date => date.format('YYYY-MM-DD HH:mm:ss'));
    }
  },
  methods: {
    month(date) {
      let month = date.format("MMMM");
      month = this.$helper.string.lcfirst(month);
      return this.$t("months." + month);
    },
    onInput(values) {
      if (values.length === 2) {
        this.$store.dispatch("retour/view", values);
        this.$refs.calendar.close();
      }
    }
  }
};
</script>

<style lang="scss">
.retour-timeframe .k-dropdown-content {
  top: calc(100% + .5rem);
}
</style>
