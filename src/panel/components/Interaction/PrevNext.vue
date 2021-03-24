<template>
  <k-button-group class="prevnext">
    <k-button 
      icon="angle-left" 
      :disabled="!hasPrev" 
      @click="onNavigate('subtract')" 
    />
    
    <k-button
      v-for="unit in units"
      :key="unit"
      :current="unit === current"
      @click="set(unit)"
    >
      {{ $t('retour.stats.mode.' + unit) }}
    </k-button>

    <k-button 
      icon="angle-right" 
      :disabled="!hasNext" 
      @click="onNavigate('add')"
    />
  </k-button-group>
</template>

<script>
export default {
  computed: {
    current() {
      const unit = this.$store.getters["retour/unit"];

      if (
        unit === "custom" &&
        this.$store.getters["retour/isAll"] === true
      ) {
        return "all";
      }

      return unit;
    },
    units() {
      return ["all", "year", "month", "week", "day"];
    },
    hasPrev() {
      return this.$store.getters["retour/hasPrev"];
    },
    hasNext() {
      return this.$store.getters["retour/hasNext"];
    }
  },
  methods: {
    set(unit) {
      const view = {
        from: this.$library.dayjs().startOf("day"),
        to:   this.$library.dayjs().endOf("day")
      };

      switch (unit) {
        case "all":
          const meta = this.$store.state.retour.meta;
          view.from  = this.$library.dayjs(meta.first).startOf("day");
          view.to    = this.$library.dayjs(meta.last).endOf("day");
          break;
        case "year":
          view.from = view.from.startOf("year");
          view.to   = view.to.endOf("year");
          break;
        case "month":
          view.from = view.from.startOf("month");
          view.to   = view.to.endOf("month");
          break;
        case "week":
          if (view.from.day() === 0) {
            view.from = view.from.subtract(6, "day");
          } else {
            view.from = view.from.subtract(view.from.day() - 1, "day");
            view.to = view.to.add(7 - view.to.day(), "day");
          }
          break;
      }

      return this.$store.dispatch("retour/view", view);
    },
    onNavigate(method) {
      let unit   = this.current;
      let factor = 1;

      if (unit === "week") {
        factor = 7;
        unit   = "day";
      }

      const view = this.$store.state.retour.view;
      view.from  = view.from[method](factor, unit).startOf(unit);
      view.to    = view.to[method](factor, unit).endOf(unit);

      this.$store.dispatch("retour/view", view);
    }
  }
}
</script>

<style>
.retour .prevnext [aria-current] {
  font-weight: 600;
}
.retour .prevnext .k-button-text {
  opacity: 1;
}
</style>
