<template>
  <div class="rt-calendar k-calendar-input">
    <nav>
      <k-button icon="angle-left" @click="prev" />
      <span class="k-calendar-selects">
        <k-select-input
          :options="months"
          :disabled="disabled"
          :required="true"
          v-model.number="month"
        />
        <k-select-input
          :options="years"
          :disabled="disabled"
          :required="true"
          v-model.number="year"
        />
      </span>
      <k-button icon="angle-right" @click="next" />
    </nav>
    <table class="k-calendar-table">
      <thead>
        <tr>
          <th v-for="day in weekdays" :key="'weekday_' + day">{{ day }}</th>
        </tr>
      </thead>
      <tbody @mouseleave="hover = null">
        <tr v-for="week in numberOfWeeks" :key="'week_' + week">
          <td
            v-for="(day, dayIndex) in days(week)"
            :key="'day_' + dayIndex"
            :aria-current="isToday(day)"
            :aria-selected="isCurrent(day)"
            :data-intersected="isIntersected(day)"
            :data-isStart="isStart(day)"
            :data-isEnd="isEnd(day)"
            @mouseover="setHover(day)"
            class="k-interval-day"
          >
            <k-button v-if="day" @click="select(day)">
              {{ day }}
            </k-button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  props: {
    from: Object,
    to: Object
  },
  data() {
    const start = this.from ? this.getDate(
      this.from.date(),
      this.from.month(),
      this.from.year()
    ) : null;

    const end = this.to ? this.getDate(
      this.to.date(),
      this.to.month(),
      this.to.year()
    ) : null;

    const current = start ? start : end ? end : this.$library.dayjs();

    return {
      month: current.month() + 1,
      year: current.year(),
      start: start,
      end: end,
      today: this.$library.dayjs(),
      hover: null,
    };
  },
  computed: {
    date() {
      return this.getDate(1);
    },
    numberOfDays() {
      return this.date.daysInMonth();
    },
    numberOfWeeks() {
      return Math.ceil((this.numberOfDays + this.firstWeekday - 1) / 7);
    },
    firstWeekday() {
      const weekday = this.date.clone().startOf('month').day();
      return weekday > 0 ? weekday : 7;
    },
    weekdays() {
      return [
        this.$t('days.mon'),
        this.$t('days.tue'),
        this.$t('days.wed'),
        this.$t('days.thu'),
        this.$t('days.fri'),
        this.$t('days.sat'),
        this.$t('days.sun'),
      ];
    },
    monthnames() {
      return [
        this.$t('months.january'),
        this.$t('months.february'),
        this.$t('months.march'),
        this.$t('months.april'),
        this.$t('months.may'),
        this.$t('months.june'),
        this.$t('months.july'),
        this.$t('months.august'),
        this.$t('months.september'),
        this.$t('months.october'),
        this.$t('months.november'),
        this.$t('months.december'),
      ];
    },
    months() {
      var options = [];

      this.monthnames.forEach((item, index) => {
        options.push({
          value: index,
          text: item
        });
      });

      return options;
    },
    years() {
      var options = [];

      for (var x = this.year - 10; x <= this.year + 10; x++) {
        options.push({
          value: x,
          text: this.$helper.pad(x)
        });
      }

      return options;
    }
  },
  methods: {
    days(week) {
      let days = [];
      let start = (week - 1) * 7 + 1;

      for (var x = start; x < start + 7; x++) {
        var day = x - (this.firstWeekday - 1);
        if (day <= 0 || day > this.numberOfDays) {
          days.push("");
        } else {
          days.push(day);
        }
      }

      return days;
    },
    next() {
      let next = this.date.add(1, 'month');
      this.set(next);
    },
    getDate(day, month = this.month, year = this.year) {
      return this.$library.dayjs(new Date(
        year,
        month,
        day,
        0,
        0
      ));
    },
    setHover(day) {
      this.hover = this.getDate(day);
    },
    isStart(day) {
      const date = this.getDate(day);

      if (date.isSame(this.start)) {
        return true;
      }

     if (this.start && this.end) {
        return false;
      }

      return !this.start && date.isSame(this.hover) && date.isBefore(this.end);
    },
    isEnd(day) {
      const date = this.getDate(day);

      if (date.isSame(this.end)) {
        return true;
      }

      if (this.start && this.end) {
        return false;
      }

      return date.isSame(this.hover) && date.isAfter(this.start);
    },
    isToday(day) {
      return this.getDate(day).isSame(this.today);
    },
    isInRange(date, from, to) {
      if (from && to) {
        return (
          date.isSame(from) ||
          date.isSame(to) ||
          (date.isAfter(from) &&
          date.isBefore(to))
        );
      }

      if (from) {
        return date.isSame(from);
      }

      if (to) {
        return date.isSame(to);
      }

      return false;
    },
    isCurrent(day) {
      if (day === "") {
        return false;
      }

      return this.isInRange(
        this.getDate(day),
        this.start,
        this.end
      );
    },
    isIntersected(day) {
      if (day === "") {
        return false;
      }

      if (this.start && this.end) {
        return false;
      }

      if (this.start && this.hover) {
        if (this.hover.isAfter(this.start)) {
          return this.isInRange(
            this.getDate(day),
            this.start,
            this.hover
          );
        }
      }

      if (this.end && this.hover) {
        if (this.hover.isBefore(this.end)) {
          return this.isInRange(
            this.getDate(day),
            this.hover,
            this.end
          );
        }
      }

      return false;
    },
    prev() {
      let prev = this.date.subtract(1, 'month');
      this.set(prev);
    },
    set(date) {
      this.day   = date.date();
      this.month = date.month();
      this.year  = date.year();
    },
    select(day) {
      const date = this.getDate(day);

      if (this.start && this.end) {
        this.start = date;
        this.end   = null;
      } else if (this.start) {
        if (date.isBefore(this.start)) {
          this.start = date;
        } else {
          this.end = date;
        }
      } else {
        if (date.isAfter(this.end)) {
          this.end = date;
        } else {
          this.start = date;
        }
      }

      this.onSelect();
    },
    onSelect() {
      this.$emit("select", {
        from: this.start,
        to: this.end
      });
    }
  }
};
</script>

<style lang="scss">

.k-interval-day .k-button {
  width: 2rem;
  height: 2rem;
  margin: 0 auto;
  color: #fff;
  line-height: 1.75rem;
  display: flex;
  justify-content: center;
  border-radius: 50%;
  border: 2px solid transparent;
}
.k-interval-day .k-button .k-button-text {
  opacity: 1;
}
.k-interval-day:hover .k-button {
  border-color: rgba(#fff, 0.25);
}
.k-interval-day[data-intersected] {
  background-color: rgba(#fff, 0.25);
}
.k-interval-day[aria-current] .k-button {
  color: var(--color-focus-light);
  font-weight: 500;
}
.k-interval-day[aria-selected] {
  background-color: var(--color-focus-light);
  color: #fff;
}
.k-interval-day[data-isstart] {
  border-top-left-radius: 100%;
  border-bottom-left-radius: 100%;
}
.k-interval-day[data-isend] {
  border-top-right-radius: 100%;
  border-bottom-right-radius: 100%;
}
</style>
