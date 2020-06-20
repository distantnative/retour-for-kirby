<template>
  <div class="k-calendar-input retour-calendar bg-black rounded-sm">
    <nav>
      <k-button
        icon="angle-left"
        @click="prev"
      />
      <span class="k-calendar-selects">
        <k-select-input
          v-model.number="month"
          :options="months"
          :disabled="disabled"
          :required="true"
        />
        <k-select-input
          v-model.number="year"
          :options="years"
          :disabled="disabled"
          :required="true"
        />
      </span>
      <k-button
        icon="angle-right"
        @click="next"
      />
    </nav>
    <table class="k-calendar-table">
      <thead>
        <tr>
          <th
            v-for="dayHeader in weekdays"
            :key="'weekday_' + dayHeader"
          >
            {{ dayHeader }}
          </th>
        </tr>
      </thead>
      <tbody @mouseleave="hover = null">
        <tr
          v-for="week in numberOfWeeks"
          :key="'week_' + week"
        >
          <td
            v-for="(dayButton, dayIndex) in days(week)"
            :key="'day_' + dayIndex"
            :aria-current="isToday(dayButton) ? 'date' : false"
            :aria-selected="isCurrent(dayButton) ? 'date' : false"
            :data-is-intersected="isIntersected(dayButton)"
            :data-is-begin="isBegin(dayButton)"
            :data-is-end="isEnd(dayButton)"
            class="k-calendar-day"
            @mouseover="onHover(dayButton)"
          >
            <k-button
              v-if="dayButton"
              :text="dayButton"
              @click="select(dayButton)"
            />
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td
            class="k-calendar-today"
            colspan="7"
          >
            <k-button
              :text="$t('today')"
              @click="selectToday"
            />
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<script>
export default {
  extends: "k-calendar",
  props: {
    value: Object
  },
  data() {
    const data = {
      begin: this.getDate(
        this.value.begin.date(),
        this.value.begin.month(),
        this.value.begin.year()
      ),
      end: this.getDate(
        this.value.end.date(),
        this.value.end.month(),
        this.value.end.year()
      ),
      today: this.$library.dayjs().startOf("day"),
    };

    return {
      ...data,
      month: data.begin.month(),
      year: data.begin.year(),
      hover: null
    };
  },
  computed: {
    date() {
      return this.getDate(1);
    }
  },
  methods: {
    getDate(day, month = this.month, year = this.year) {
      return this.$library.dayjs(new Date(
        year,
        month,
        day,
        0,
        0,
        0
      ));
    },
    isBegin(day) {
      const date = this.getDate(day);
      return date.isSame(this.begin);
    },
    isCurrent(day) {
      const date = this.getDate(day);
      return date.isSame(this.begin) || date.isSame(this.end);
    },
    isEnd(day) {
      const date = this.getDate(day);

      if (this.end && date.isSame(this.end)) {
          return true;
      }

      if (!this.end && date.isSame(this.hover) && date.isAfter(this.begin)) {
        return true;
      }

      return false;
    },
    isIntersected(day) {
      if (day === "") {
        return false;
      }

      const date = this.getDate(day);

      if (this.begin && this.end) {
        return this.isInRange(date, this.begin, this.end);
      }

      if (
        this.begin &&
        this.hover &&
        this.hover.isAfter(this.begin)
      ) {
        return this.isInRange(date, this.begin, this.hover);
      }

      if (
        this.end && this.hover && this.hover.isBefore(this.end)
      ) {
        return this.isInRange(date, this.hover, this.end);
      }

      return false;
    },
    isInRange(date, a, b) {
      if (a && b) {
        return (
          date.isSame(a) ||
          date.isSame(b) ||
          (
            date.isAfter(a) &&
            date.isBefore(b)
          )
        );
      }

      if (a) {
        return date.isSame(a);
      }

      if (to) {
        return date.isSame(b);
      }

      return false;
    },
    onHover(day) {
      this.hover = this.getDate(day);
    },
    select(day) {
      const date = this.getDate(day);

      // existing selection
      if (this.begin && this.end) {
        this.begin = date;
        this.end   = null;

      // begin is already selected
      } else if (this.begin) {
        if (date.isBefore(this.begin)) {
          this.begin = date;
        } else {
          this.end = date;
        }

      } else {
        this.begin = date;
      }

      if (this.begin && this.end) {
        this.$emit("input", { begin: this.begin, end: this.end });
      }
    },
  }
}
</script>

<style lang="scss">
.retour-calendar .k-calendar-day[data-is-begin] {
  border-top-left-radius: 100%;
  border-bottom-left-radius: 100%;
}
.retour-calendar .k-calendar-day[data-is-end] {
  border-top-right-radius: 100%;
  border-bottom-right-radius: 100%;
}
.retour-calendar .k-calendar-day[data-is-intersected] {
  background-color: rgba(#fff, 0.25);
}
</style>
