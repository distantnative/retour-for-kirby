<template>
  <k-button-group layout="collapsed" class="k-retour-timespan">
    <k-dropdown>
      <k-button
        :dropdown="true"
        :text="timespan.label"
        icon="calendar"
        size="xs"
        variant="filled"
        @click="$refs.units.toggle()"
      />

      <k-dropdown-content ref="units" x-align="end">
        <k-dropdown-item
          v-for="unit in ['all', 'year', 'month', 'day']"
          v-if="unit !== 'all' || timespan.hasAll"
          :key="unit"
          :current="isCurrentUnit(unit)"
          :icon="icon(unit)"
          size="xs"
          variant="filled"
          @click="set(unit)"
        >
          {{ $t("retour.stats.mode." + unit) }}
        </k-dropdown-item>
        <hr />
        <k-dropdown-item
          size="xs"
          icon="merge"
          variant="filled"
          @click="() => set('today')"
        >
          {{ $t("retour.timespan.today.label") }}
        </k-dropdown-item>
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
      :disabled="!timespan.hasPrev || timespan.isAll"
      @click="navigate('subtract')"
    />
    <k-button
      :disabled="!timespan.hasNext || timespan.isAll"
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
  methods: {
    icon(unit) {
      if (this.isCurrentUnit(unit) === true) {
        return this.timespan.isCurrent ? "circle-focus" : "circle-nested";
      }

      return "circle";
    },
    isCurrentUnit(unit) {
      return (
        unit === this.timespan.unit || (unit === "all" && this.timespan.isAll)
      );
    },
    navigate(method) {
      const unit = this.timespan.unit;
      const from = this.$library.dayjs(this.timespan.from);
      const to = this.$library.dayjs(this.timespan.to);

      this.update({
        from: from[method](1, unit).startOf(unit),
        to: to[method](1, unit).endOf(unit),
      });
    },
    set(unit) {
      if (unit === "all") {
        return this.update({
          from: this.$library.dayjs(this.timespan.first),
          to: this.$library.dayjs(this.timespan.last),
        });
      }

      let date = this.$library.dayjs(this.timespan.from);

      // on hitting the current unit again,
      // move to current timespan around today
      if (unit === this.timespan.unit || unit === "today") {
        unit = this.timespan.unit;
        date = this.$library.dayjs();
      }

      this.update({
        from: date.startOf(unit),
        to: date.endOf(unit),
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
