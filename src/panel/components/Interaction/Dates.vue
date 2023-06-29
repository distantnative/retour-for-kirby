<template>
  <span class="retour-dates">
    <k-icon type="calendar" />
    {{ label }}
  </span>
</template>

<script>
export default {
  props: {
    dates: Object,
    timespan: Object,
  },
  computed: {
    label() {
      const from = this.dates.from;
      const to = this.dates.to;

      if (this.timespan.unit === "day") {
        return `${from.format("D")} ${this.month(from)} ${from.format("YYYY")}`;
      }

      if (this.timespan.unit === "month") {
        return `${this.month(from)} ${from.format("YYYY")}`;
      }

      if (this.timespan.unit === "year") {
        return `${from.format("YYYY")}`;
      }

      // within same month
      if (from.isSame(to, "month")) {
        return `
        ${from.format("D")} - ${to.format("D")}
        ${this.month(to)} ${to.format("YYYY")}
        `;
      }

      // within same year
      if (from.isSame(to, "year")) {
        return `
        ${from.format("D")} ${this.month(from)} -
        ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}
        `;
      }

      return `
      ${from.format("D")} ${this.month(from)} ${from.format("YYYY")} -
      ${to.format("D")} ${this.month(to)} ${to.format("YYYY")}`;
    },
    value() {
      return Object.values(this.dates).map((date) =>
        date.format("YYYY-MM-DD HH:mm:ss")
      );
    },
  },
  methods: {
    month(date) {
      date = date.format("MMMM");
      date = this.$helper.string.lcfirst(date);
      return this.$t("months." + date);
    },
  },
};
</script>

<style>
.retour-dates {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}
</style>
