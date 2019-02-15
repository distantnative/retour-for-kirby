<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">
        {{ $t('rt.fails') }}
      </label>

      <k-button-group>
        <k-button
          v-for="btn in ['failed', 'last']"
          :key="btn"
          :current="by === btn"
          icon="funnel"
          @click="sort(btn)"
        >
          {{ $t('rt.fails.sort.' + btn) }}
        </k-button>
      </k-button-group>
    </header>

    <table class="k-structure-table">
      <thead>
        <tr>
          <th class="k-structure-table-index">
            #
          </th>
          <th
            v-for="column in columns"
            :key="column.name"
            :data-width="column.width"
            class="k-structure-table-column"
          >
            {{ $t('rt.fails.' + column.name) }}
          </th>
          <th />
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in paginatedItems" :key="index">
          <td class="k-structure-table-index">
            <span class="k-structure-table-index-number">
              {{ index + 1 }}
            </span>
          </td>
          <td class="k-structure-table-column">
            <k-url-field-preview :value="item.path" />
          </td>
          <td class="k-structure-table-column">
            <k-url-field-preview :value="item.referrer" />
          </td>
          <td class="k-structure-table-column">
            <k-rt-count-field-preview
              :value="{
                hits: `${item.failed + item.redirected} (${item.redirected})`,
                last: item.last
              }"
            />
          </td>
          <td class="k-structure-table-option">
            <k-button
              :disabled="!canUpdate"
              :tooltip="$t('add')"
              icon="add"
              @click="add(item)"
            />
          </td>
        </tr>
      </tbody>
    </table>

    <k-pagination v-bind="pagination" @paginate="paginateItems" />
  </div>
</template>

<script>
export default {
  props: {
    canUpdate: Boolean,
    fails: Array,
    options: Object,
  },
  data() {
    return {
      page: 1,
      by: "failed"
    }
  },
  computed: {
    columns() {
      return [
        { name: "path",     width: "1/3" },
        { name: "referrer", width: "1/3" },
        { name: "count",    width: "1/6" }
      ];
    },
    pagination() {
      return {
        page: this.page,
        limit: this.options.limit,
        total: this.fails.length,
        align: "center",
        details: true
      };
    },
    paginatedItems() {
      const index  = this.page - 1;
      const offset = index * this.options.limit;
      return this.fails.slice(offset, offset + this.options.limit);
    }
  },
  methods: {
    add(fail) {
      this.$emit("go", ["redirects", (view) => {
        const field = view.$refs.redirects.$refs.field;
        field.currentIndex = "new";
        field.currentModel = { from: fail.path, status: "disabled" };
        field.createForm("to");
      }]);
    },
    paginateItems(pagination) {
      this.page = pagination.page;
    },
    sort(by) {
      this.$emit("sort", by);
      this.by = by;
      this.page = 1;
    }
  }
}
</script>
