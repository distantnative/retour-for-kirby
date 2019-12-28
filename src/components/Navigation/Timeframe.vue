<template>
  <div v-if="show" class="rt-calendar-wrapper">
    <div @click.stop="close" class="rt-calendar-label">
      <k-icon type="calendar" /> {{ display(from, to) }}
    </div>

    <calendar
      ref="calendar"
      :from="from"
      :to="to"
      @select="select"
    />
  </div>

  <div v-else @click="open" class="rt-calendar-label">
    <k-icon type="calendar" /> {{ active }}
  </div>
</template>

<script>
import Calendar from "./Calendar.vue";

export default {
  components: {
    Calendar
  },
  data() {
    return {
      from: null,
      to: null,
      show: false
    }
  },
  computed: {
    active() {
      return this.display(
        this.$store.state.retour.view.from,
        this.$store.state.retour.view.to
      );
    }
  },
  methods: {
    onClick(e) {
      if (this.$refs.calendar) {
        e.stopPropagation();

        if (!this.$refs.calendar.$el.contains(e.target)) {
          this.close();
        }
      }
    },
    display(from, to) {
      if (!from && !to) {
        return "...";
      }

      if (!to) {
        return from.format("ll") + " –";
      }

      if (!from) {
        return "– " + to.format("ll");
      }

      if (
        from.isSame(to, "date") &&
        from.isSame(to, "month") &&
        from.isSame(to, "year")
      ) {
        return from.format("LL");
      }

      if (
        from.isSame(to, "month") &&
        from.isSame(to, "year") &&
        from.date() === 1 &&
        to.date() === to.daysInMonth()
      ) {
        return from.format("MMMM YYYY");
      }

      if (
        from.isSame(to, "year") &&
        from.date() === 1 &&
        from.month() === 0 &&
        to.date() === 31 &&
        to.month() === 11
      ) {
        return from.format("YYYY");
      }

      if (
        from.isSame(to, "month") &&
        from.isSame(to, "year")
      ) {
        return from.format("D") + " - " + to.format("ll");
      }

      // if (
      //   from.isSame(to, "year")
      // ) {
      //   return from.format("D MMM") + " - " + to.format("D MMM YYYY");
      // }

      return from.format("ll") + " - " + to.format("ll");
    },
    month(date) {
      let month = date.format("MMMM");
      month = this.$helper.string.lcfirst(month);
      return this.$t("months." + month)
    },
    open() {
      this.from = this.$store.state.retour.view.from;
      this.to   = this.$store.state.retour.view.to;
      this.show = true;
    },
    close() {
      if (this.from && this.to) {
        this.$store.dispatch("retour/timeframe", {
          from: this.from,
          to: this. to
        });
        this.show = false;
      }
    },
    select(dates) {
      this.from = dates.from;
      this.to   = dates.to;
      this.close();
    }
  }
}
</script>

<style lang="scss">
.rt-calendar {
  position: absolute;
  width: 16em;
  top: 2.2em;
  z-index: 100;
}
.rt-calendar-wrapper {
  position: relative;
}
.rt-calendar-label {
  display: flex;
  cursor: pointer;

  > .k-icon {
    margin-right: .75rem;
    opacity: .2;
  }
}
</style>
