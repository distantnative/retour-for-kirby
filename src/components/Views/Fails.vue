<template>
  <div>
    <header class="k-field-header">
      <label class="k-field-label">{{ $t('retour.fails') }}</label>

      <k-button-group>
        <k-button
          icon="funnel"
          :current="sort === 'fails'"
          @click="sortBy('fails')"
        >
          By hits
        </k-button>
        <k-button
          icon="funnel"
          :current="sort === 'last'"
          @click="sortBy('last')"
        >
          By date
        </k-button>
      </k-button-group>
    </header>

    <table class="k-structure-table">
      <thead>
        <tr>
          <th class="k-structure-table-index">#</th>
          <th data-width="1/3" class="k-structure-table-column">
            Path
          </th>
          <th data-width="1/3" class="k-structure-table-column">
            Referrer
          </th>
          <th data-width="1/6" class="k-structure-table-column">
            Hits (Redirected)
          </th>
          <th />
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in paginatedItems" :key="index">
          <td class="k-structure-table-index">
            <span class="k-structure-table-index-number">{{ index + 1 }}</span>
          </td>
          <td class="k-structure-table-column" data-width="">
            <k-url-field-preview :value="item.path" :column="{}" />
          </td>
          <td class="k-structure-table-column" data-width="">
            <k-url-field-preview :value="item.referrer" :column="{}" />
          </td>
          <td class="k-structure-table-column" data-width="">
            <k-retour-hits-field-preview :value="{
              hits: `${item.fails + item.redirects} (${item.redirects})`,
              last: item.last
            }" />
          </td>
          <td class="k-structure-table-option">
            <k-button :disabled="true" :tooltip="$t('add')" @click="$emit('add', item)" />
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
    options: Object
  },
  data() {
    return {
      items: [],
      sort:  'fails',
      page:  1
    }
  },
  computed: {
    pagination() {
      return {
        page: this.page,
        limit: this.options.limit,
        total: this.items.length,
        align: "center",
        details: true
      };
    },
    paginatedItems() {
      const index = this.page - 1;
      const offset = index * this.options.limit;
      return this.items.slice(offset, offset + this.options.limit);
    }
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$events.$emit('retour-load');
      this.$api.get('retour/fails/' + this.sort).then(response => {
        this.items = response;
        this.page  = 1;
        this.$events.$emit('retour-loaded');
      });
    },
    paginateItems(pagination) {
      this.page = pagination.page;
    },
    sortBy(sort) {
      this.sort = sort;
      this.fetch();
    }
  }
}
</script>

