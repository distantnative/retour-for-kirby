<template>
  <section class="k-tbl">

    <!-- Section header with filter UI -->
    <header class="k-section-header">
      <k-headline>{{ headline }}</k-headline>

      <slot name="replace-filter">
        <k-button-group>
          <k-button :disbaled="true" icon="cirle" />
          <button
            v-if="showReset"
            class="k-tbl-reset"
            @click="reset"
          >
            Reset
          </button>
          <input
            v-if="showSearch"
            ref="search"
            v-model="searchTerm"
            type="text"
            placeholder="Filter items…"
            class="k-tbl-search"
            @keydown.esc="reset"
          >
          <k-button
            v-if="showAdd"
            icon="add"
            @click="$emit('add')"
          >
            {{ $t("add") }}
          </k-button>
        </k-button-group>
      </slot>
    </header>

    <slot name="replace-table">

      <!-- Table head -->
      <table>
        <thead>
          <th
            v-for="column in columns"
            :key="column.field"
            :data-align="column.align || 'left'"
            :data-width="column.width"
            :data-responsive="column.responsive !== false"
            :data-sortable="column.sort !== false"
            :data-sorted="sortBy === column ? (sortOrder === 1 ? 'asc' : 'desc') : false"
            @click="column.sort !== false && sort(column)"
          >
            {{ column.label }}
          </th>
          <th
            v-if="actions"
            :data-width="config.inlineActions ? actions.length + 'fr' : '1fr'"
          />
        </thead>
        <tbody>

          <!-- Empty row placeholder -->
          <tr v-if="rowsFiltered.length === 0">
            <td data-align="center" :colspan="this.columns.length + (this.actions ? 1 : 0)">
              <slot name="empty-row">
                <p>
                  <k-icon v-if="this.isLoading" type="loader" />
                  <span v-else>No rows available</span>
                </p>
              </slot>
            </td>
          </tr>

          <!-- Rows -->
          <tr
            v-else
            v-for="(row, index) in rowsPaginated"
            :key="'row_' + index"
          >
            <td
              v-for="column in columns"
              :key="column.field"
              :data-width="column.width"
              :data-align="column.align || 'left'"
              :data-responsive="column.responsive !== false"
              @click="$emit('action', [config.rowClick, row, column.field])"
            >
              <slot
                :name="'field-' + (column.name || column.field)"
                :index="index"
                :column="column"
                :field="column.field"
                :row="row"
                :value="value(row[column.field], column.type)"
              >
                <k-url-field-preview
                  v-if="column.type === 'url'"
                  :value="row[column.field]"
                />
                <p
                  v-else
                  v-html="column.html || value(row[column.field], column.type)"
                />
              </slot>
            </td>

            <!-- Actions options -->
            <slot name="column-options" :row="row">
              <td
                v-if="actions"
                :data-width="config.inlineActions ? actions.length + 'fr' : '1fr'"
                data-align="center"
                class="k-tbl-options"
              >
                <template v-if="config.inlineActions">
                  <k-button
                    v-for="action in actions" :key="action.click"
                    :tooltip="action.text"
                    :icon="action.icon"
                    @click.stop="$emit('action', [action.click, row])"
                  />
                </template>
                <template v-else>
                  <k-button
                    :tooltip="$t('options')"
                    icon="dots"
                    @click.stop="$refs['options-' + index][0].toggle()"
                  />
                  <k-dropdown-content
                    :ref="'options-' + index"
                    :options="actions"
                    align="right"
                    @action="$emit('action', [$event, row])"
                  />
                </template>
              </td>
            </slot>
          </tr>
        </tbody>
      </table>
    </slot>

    <!-- Section footer with pagination UI -->
    <footer class="k-tbl-footer">
      <slot name="replace-footer">
        <template v-if="rowsFiltered.length > 0">
          <div class="k-tbl-perPage">
            <span>Rows displayed</span>
            <select autocomplete="off" v-model="perPage" @input="page = 1">
              <option v-for="step in config.perPage" :key="step" :value="step">
                {{ step }}
              </option>
              <option :value="this.rowsFiltered.length">All</option>
            </select>
          </div>

          <div class="k-tbl-navigation">
            <div
              :data-disabled="page <= 1"
              class="btn btn-prev"
              @click="page -= 1"
            >
              <span class="chevron"></span>
              <span>{{ $t('prev') }}</span>
            </div>
            <div class="info">
              {{ this.pageRowFirst }} - {{ Math.min(this.pageRowLast, this.rowsFiltered.length) }} of {{ this.rowsFiltered.length }}
            </div>
            <div
              :data-disabled="page * perPage >= rowsFiltered.length"
              class="btn btn-next"
              @click="page += 1"
            >
              <span>{{ $t('next') }}</span>
              <span class="chevron"></span>
            </div>
          </div>
        </template>
      </slot>
    </footer>

    <slot />
  </section>
</template>

<script>
export default {
  props: {
    headline: String,
    columns: Array,
    rows: Array,
    options: Object,
    actions: Array,
    isLoading: {
      type: Boolean,
      default: false
    }
  },
  data(){
    return {
      searchTerm: "",
      sortBy: null,
      sortOrder: 1,
      page: 1,
      perPage: 10
    }
  },
  computed: {
    config() {
      return {
        add: false,
        initialSort: null,
        inlineActions: false,
        perPage: [5, 10, 25, 50, 100],
        ...this.options
      }
    },
    pageOffset() {
      return (this.page - 1) * this.perPage;
    },
    pageRowFirst() {
      return this.pageOffset + 1;
    },
    pageRowLast() {
      return this.pageOffset + this.perPage;
    },
    rowsFiltered() {
      if (this.searchTerm === "") {
        return this.rows;
      }

      const q = this.searchTerm.toLowerCase();

      return this.rows.slice().filter(x => {
        let include = false;

        Object.keys(x).forEach(field => {
          const column = this.columns.find(column => column.field === field);
          if (!column || column.search === false) {
            return;
          }
          if (String(x[field]).toLowerCase().includes(q)) {
            include = true;
          }
        });

        return include;
      });
    },
    rowsPaginated() {
      return this.rowsSorted.slice(this.pageOffset, this.pageOffset + this.perPage);
    },
    rowsSorted() {
      if (!this.sortBy) {
        return this.rowsFiltered;
      }

      return this.rowsFiltered.slice().sort((a, b) => {
        if (this.sortBy.field) {
          a = a[this.sortBy.field];
          b = b[this.sortBy.field];
        }

        if (this.sortBy.sort && this.sortBy.sort.field) {
          a = a[this.sortBy.sort.field];
          b = b[this.sortBy.sort.field];
        }

        if (this.sortBy.sort && this.sortBy.sort.value) {
          a = a[this.sortBy.sort.value];
          b = b[this.sortBy.sort.value];
        }

        switch (this.sortBy.type) {
          case "number":
            return (a - b) * this.sortOrder;
            break;

          case "date":
            return (new Date(a) - new Date(b)) * this.sortOrder;
            break;

          default:
            a = ('' + a).toLowerCase();
            b = ('' + b).toLowerCase();
            return a.localeCompare(b) * this.sortOrder;
            break;
        }
      });
    },
    showAdd() {
      return this.config.add;
    },
    showReset() {
      return this.searchTerm !== '' || this.sortBy !== null;
    },
    showSearch() {
      return true;
    }
  },
  watch: {
    searchTerm(term) {
      this.$emit("onFilter", term);
    }
  },
  mounted() {
    const sort = this.config.initialSort;
    if (sort) {
      let column = this.columns.find(column => column.name === sort);
      if (!column) {
        column = this.columns.find(column => column.field === sort);
      }
      this.sort(column);
    }
  },
  methods: {
    reset() {
      Object.assign(this.$data, this.$options.data.apply(this));
      this.$refs.search.blur();
      this.$emit("onReset");
    },
    sort(column) {
      if (this.sortBy === column) {
        this.sortOrder *= -1;
        return;
      }

      this.sortBy = column;
      this.sortOrder = ((column.sort && column.sort.order) ? column.sort.order : column.sort) === "desc" ? -1 : 1;
      this.$emit("onSort", {
        ...column,
        currentDirection: this.sortOrder
      });
    },
    value(value, type) {
      if (!value) {
        return;
      }

      if (type === "date") {
          return (new Date(value)).toLocaleString(this.$user.language, {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      }

      return value;
    }
  }
};
</script>

<style lang="scss">
$color-white: #fff;
$color-light: #efefef;
$color-light-grey: #999;
$color-dark-grey: #777;
$color-dark: #16171a;
$color-black: #000;
$color-background: $color-light;

$font-size-small: 0.875rem;
$structure-item-height: 40px;
$breakpoint-medium: 65em;

@mixin text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.k-tbl table {
	position: relative;
	font-size: $font-size-small;
	border-spacing: 0;
	table-layout: fixed;
  width: 100%;
  max-width: 100%;

  th, td {
    position: relative;
    width: auto;
    line-height: 1.25rem;
    @include text-truncate;

    &[data-width="1fr"]  { width: 40px; }
    &[data-width="2fr"]  { width: 80px; }
    &[data-width="3fr"]  { width: 120px; }
    &[data-width="4fr"]  { width: 160px; }
    &[data-width="1/10"] { width: 10%; }
    &[data-width="1/8"]  { width: calc(100% / 8); }
    &[data-width="1/6"]  { width: calc(100% / 6); }
    &[data-width="1/5"]  { width: 20%; }
    &[data-width="1/4"]  { width: 25%; }
    &[data-width="1/3"]  { width: calc(100% / 3); }
    &[data-width="2/5"]  { width: 40%; }
    &[data-width="1/2"]  { width: 50%; }
    &[data-width="3/5"]  { width: 60%; }
    &[data-width="2/3"]  { width: calc(100% / 3 * 2); }
    &[data-width="3/4"]  { width: 75%; }
    &[data-width="4/5"]  { width: 80%; }

    &[data-align="left"]   { text-align: left; }
    &[data-align="center"] { text-align: center; }
    &[data-align="right"]  { text-align: right; }

    @media screen and (max-width: $breakpoint-medium) {
      display: none;

      &[data-responsive] {
        display: table-cell;
      }

      &[data-width="1/10"],
      &[data-width="1/8"],
      &[data-width="1/6"],
      &[data-width="1/5"],
      &[data-width="1/4"],
      &[data-width="1/3"],
      &[data-width="2/5"],
      &[data-width="1/2"],
      &[data-width="3/5"],
      &[data-width="2/3"],
      &[data-width="3/4"],
      &[data-width="4/5"] {
        width: auto !important;
      }
    }
  }

  th {
    position: relative;
    font-weight: 400;
    color: $color-dark-grey;
    padding: 0 0.75rem;
    height: $structure-item-height;
    background: #ddd;
    position: sticky;
    padding: .5rem .75rem;
    top: 0;
    z-index: 10;

    &:not(:last-child) {
      border-right: 1px solid darken(#eaeaea, 8%);
    }

    &[data-sortable] {
      cursor: pointer;

      &[data-sorted] {
        color: black;
        font-weight: 600;

        &:after {
          position:absolute;
          content: "";
          right: 1rem;
          top: calc(50% - 3px);
          border-color: black transparent;
          border-style: solid;
          border-width: 6px 5px 0;
        }
      }

      &[data-sorted="asc"] {
        &:after {
          transform: rotate(180deg);
        }
      }

      &[data-align="right"]{
        &[data-sorted] {
          &:after {
            right: auto;
            left: 1rem;
          }
        }
      }
    }

  }

  td {
    height: $structure-item-height;
    border-top: 1px solid $color-background;
    padding: 0;

    &:not(:last-child) {
      border-right: 1px solid $color-background;
    }

    &.k-tbl-options {
      display: flex;
      justify-content: space-between;
			overflow: visible;
      padding: .5rem .75rem;
		}

    > p {
      width: 100%;
      padding: .5rem .75rem;
    }

		.k-list-item-options {
			> button:first-child {
				margin-left: 4px;
      }
			> button + button {
				padding-left: 6px;
			}
		}
  }

	tbody {
    margin-top: 2px;

		tr {
			background: white;
			& + tr {
				margin-top: 2px;
			}
		}
	}
}

.k-tbl-reset {
  padding: .35rem .6rem;
  background: rgba($color-dark, .05);
  border-radius: 2px;
  margin-bottom: .65rem;
  margin-right: .75rem;
  font-size: $font-size-small;
  color: $color-dark-grey;
  transition: color 0.2s ease-out;
  &:hover {
    color: $color-black;
  }
}

.k-tbl-search {
  display: inline-block;
  min-width: 150px;
  margin: 0 .75rem .65rem 0;
  padding: .35rem .75rem .35rem .5rem;
  background: none;
  border: 0;
  border-bottom: 1px solid darken(#eaeaea, 8%);
  font-size: $font-size-small;
  line-height: 1;
  vertical-align: middle;
  white-space: normal;

  & + .k-button {
    margin-right: 0;
  }

  &::placeholder {
    color: $color-light-grey;
  }

  &:focus {
    outline: none;
    border-color: $color-white;
    background: $color-white;
  }
}

.k-tbl-footer {
  display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: $font-size-small;
  padding: .35rem .25rem;
  margin-top: .5rem;
  color: $color-dark-grey;
}

.k-tbl-perPage {
  select {
    background-color: white;
    width: auto;
    padding: .5rem;
    border: 0;
    border-radius: 0;
    height: 22px;
    width: 40px;
    margin-left: 5px;
    color: black;
    box-sizing: border-box;

    &:focus {
      outline: none;
      border-color: #409eff;
    }
  }
}

.k-tbl-navigation {
  display: flex;
  user-select: none;

  .btn {
    display: flex;
    align-items: center;
    cursor: pointer;

    &[data-disabled] {
      cursor: default;
      pointer-events: none;
      opacity: 0.25;
    }

    &:hover {
      color: $color-black;
    }
    .chevron {
      margin-top: 2px;
      width: 6px;
      height: 6px;
      border: 2px solid $color-dark-grey;
      border-width: 0 2px 2px 0;
      transition: opacity 0.2s ease-out;
      opacity: 0.5;
    }

    &-prev .chevron {
      transform: rotate(135deg);
      margin-right: 5px;
    }

    &-next .chevron {
      transform: rotate(-45deg);
      margin-left: 5px;
    }
  }

  .info {
    margin: 0 1rem;
    color: $color-black;
  }
}

</style>
