<template>
  <k-button-group layout="collapsed" class="k-retour-timespan">
    <k-dropdown>
      <k-button
        :text="label"
        icon="calendar"
        size="xs"
        variant="filled"
        @click="$refs.units.toggle()"
      />

      <k-dropdown-content ref="units" x-align="end">
        <k-dropdown-item
          v-for="unit in ['all', 'year', 'month', 'day']"
          v-if="!isDisabled(unit)"
          :key="unit"
          :current="isUnit(unit)"
          :icon="isUnit(unit) ? 'circle-nested' : 'circle'"
          size="xs"
          variant="filled"
          @click="set(unit)"
        >
          {{ $t("retour.stats.mode." + unit) }}
        </k-dropdown-item>
        <hr />
        <k-dropdown-item
          size="xs"
          icon="calendar"
          variant="filled"
          @click="() => $dialog('retour/timespan')"
        >
          {{ $t("retour.timespan.set.label") }}
        </k-dropdown-item>
      </k-dropdown-content>
    </k-dropdown>

    <k-button
      icon="angle-left"
      size="xs"
      variant="filled"
      :disabled="!hasPrev || isAll"
      @click="navigate('subtract')"
    />
    <k-button
      :disabled="!hasNext || isAll"
      icon="angle-right"
      size="xs"
      variant="filled"
      @click="navigate('add')"
    />
  </k-button-group>
</template>

<script>
export default {
  props: {
    timespan: Object,
  },
  computed: {
    dates() {
      return {
        from: this.$library.dayjs(this.timespan.from),
        to: this.$library.dayjs(this.timespan.to),
      };
    },
    first() {
      return this.$library.dayjs(this.timespan.first);
    },
    last() {
      return this.$library.dayjs(this.timespan.last);
    },
    hasPrev() {
      return this.dates.from.isAfter(this.first);
    },
    hasNext() {
      // either has more data in the future
      // or today is in the future
      return (
        this.dates.to.isBefore(this.last) ||
        this.dates.to.isBefore(this.$library.dayjs())
      );
    },
    isAll() {
      return (
        this.dates.from.isSame(this.first, "day") &&
        this.dates.to.isSame(this.last, "day")
      );
    },
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
    isDisabled(unit) {
      return unit === "all" && (!this.timespan.first || !this.timespan.last);
    },
    isUnit(unit) {
      if (unit === "all") {
        return this.isAll;
      }

      return unit === this.timespan.unit;
    },
    month(date) {
      date = date.format("MMMM");
      date = this.$helper.string.lcfirst(date);
      return this.$t("months." + date);
    },
    navigate(method) {
      let unit = this.timespan.unit;
      let factor = 1;

      if (unit === "week") {
        factor = 7;
        unit = "day";
      }

      this.update({
        from: this.dates.from[method](factor, unit).startOf(unit),
        to: this.dates.to[method](factor, unit).endOf(unit),
      });
    },
    set(unit) {
      if (unit === "all") {
        return this.update({
          from: this.first,
          to: this.last,
        });
      }

      let timespan = Object.assign({}, this.dates);

      // on hitting the current unit again,
      // move to current timespan around today
      if (unit === this.timespan.unit) {
        timespan = {
          from: this.$library.dayjs(),
          to: this.$library.dayjs(),
        };
      }

      this.update({
        from: timespan.from.startOf(unit),
        to: timespan.from.endOf(unit),
      });
    },
    update({ from, to }) {
      this.$reload({
        query: {
          from: from.format("YYYY-MM-DD"),
          to: to.format("YYYY-MM-DD"),
        },
      });
    },
  },
};
</script>
