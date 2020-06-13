<template>
  <k-dropdown class="retour-timeframe">
    <k-button
      :text="label"
      icon="calendar"
      @click="$refs.calendar.open()"
    />
    <k-dropdown-content ref="calendar">
      <retour-calendar
        :value="selection"
        @input="onInput"
      />
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  computed: {
    begin() {
      return this.selection.begin;
    },
    end() {
      return this.selection.end;
    },
    label() {
      if (
        this.begin.isSame(this.end, "date") &&
        this.begin.isSame(this.end, "month") &&
        this.begin.isSame(this.end, "year")
      ) {
        return `${this.begin.format("D")} ${this.month(this.begin)} ${this.begin.format("YYYY")}`;
      }

      if (
        this.begin.isSame(this.end, "month") &&
        this.begin.isSame(this.end, "year") &&
        this.begin.date() === 1 &&
        this.end.date() === this.end.daysInMonth()
      ) {
        return `${this.month(this.begin)} ${this.begin.format("YYYY")}`;
      }

      if (
        this.begin.isSame(this.end, "year") &&
        this.begin.date() === 1 &&
        this.begin.month() === 0 &&
        this.end.date() === 31 &&
        this.end.month() === 11
      ) {
        return `${this.begin.format("YYYY")}`;
      }

      if (
        this.begin.isSame(this.end, "month") &&
        this.begin.isSame(this.end, "year")
      ) {
        return `${this.begin.format("D")} - ${this.end.format("D")} ${this.month(this.end)} ${this.end.format("YYYY")}`;
      }

      if (this.begin.isSame(this.end, "year")) {
        return `${this.begin.format("D")} ${this.month(this.begin)} - ${this.end.format("D")} ${this.month(this.end)} ${this.end.format("YYYY")}`;
      }

      return `${this.begin.format("D")} ${this.month(this.begin)} ${this.begin.format("YYYY")} - ${this.end.format("D")} ${this.month(this.end)} ${this.end.format("YYYY")}`;
    },
    selection() {
      return this.$store.state.retour.selection;
    }
  },
  methods: {
    month(date) {
      let month = date.format("MMMM");
      month = this.$helper.string.lcfirst(month);
      return this.$t("months." + month);
    },
    onInput(selection) {
      this.$store.dispatch("retour/selection", selection);
      this.$refs.calendar.close();
    }
  }
};
</script>

<style lang="scss">
.retour-timeframe .k-dropdown-content {
  top: calc(100% + .5rem);
}
</style>
