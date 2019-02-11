<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">
        {{ $t('retour.fails') }}
      </label>

      <k-button-group>
        <k-button
          v-for="by in ['failed', 'last']"
          :key="by"
          icon="funnel"
          @click="sort(by)"
        >
          {{ $t('retour.fails.sort.' + by) }}
        </k-button>
      </k-button-group>
    </header>

    <table class="k-structure-table">
      <thead>
        <tr>
          <th class="k-structure-table-index">
            #
          </th>
          <th data-width="1/3" class="k-structure-table-column">
            {{ $t('retour.fails.path') }}
          </th>
          <th data-width="1/3" class="k-structure-table-column">
            {{ $t('retour.fails.referrer') }}
          </th>
          <th data-width="1/6" class="k-structure-table-column">
            {{ $t('retour.fails.count') }}
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
          <td class="k-structure-table-column" data-width="">
            <k-url-field-preview :value="item.path" :column="{}" />
          </td>
          <td class="k-structure-table-column" data-width="">
            <k-url-field-preview :value="item.referrer" :column="{}" />
          </td>
          <td class="k-structure-table-column" data-width="">
            <k-retour-count-field-preview
              :value="{
                hits: `${item.failed + item.redirected} (${item.redirected})`,
                last: item.last
              }"
            />
          </td>
          <td class="k-structure-table-option">
            <k-button
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
    fails: Array,
    options: Object
  },
  data() {
    return {
      page: 1
    }
  },
  computed: {
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
      this.$api.post("retour/redirects", {
        from: fail.path,
        to: null,
        status: "disabled"
      }).then(() => {
        this.$emit("reload", [(view) => { view.go("redirects") }]);
      });
    },
    paginateItems(pagination) {
      this.page = pagination.page;
    },
    sort(by) {
      this.$emit("sort", by);
      this.page = 1;
    }
  }
}
</script>

