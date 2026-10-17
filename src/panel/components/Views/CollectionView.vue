<template>
	<k-retour-view
		v-bind="$props"
		:class="['k-retour-collection-view', $options.class]"
	>
		<template #buttons>
			<k-input
				v-if="searching"
				:aria-label="$t('filter')"
				:autofocus="true"
				:placeholder="$t('filter') + ' …'"
				:value="term"
				class="k-retour-search"
				type="search"
				@input="
					term = $event;
					filter();
				"
				@keydown.esc.stop="toggleSearch(true)"
			/>

			<k-button
				ref="filter"
				:title="$t('filter')"
				icon="filter"
				size="sm"
				variant="filled"
				@click="toggleSearch()"
			/>
			<k-view-buttons :buttons="buttons" />
		</template>

		<k-empty
			v-if="data.length === 0"
			v-bind="
				searchterm ? { icon: 'search', text: $t('search.results.none') } : empty
			"
			layout="table"
		/>

		<k-table
			v-else
			:columns="columns"
			:index="pagination.offset + 1"
			:rows="data"
			@cell="onCell"
			@header="onHeader"
		>
			<template #header="{ columnIndex, label }">
				<button type="button">
					{{ label }}
					<k-icon
						v-if="columnIndex === sorting[0]"
						:type="sorting[1] === 'desc' ? 'angle-down' : 'angle-up'"
					/>
				</button>
			</template>
		</k-table>

		<footer class="k-bar k-collection-footer">
			<k-pagination
				:details="true"
				:limit="pagination.limit"
				:page="pagination.page"
				:total="pagination.total"
				@paginate="reload({ page: $event.page })"
			/>
		</footer>
	</k-retour-view>
</template>

<script>
import { props as View } from "./View.vue";

export default {
	mixins: [View],
	props: {
		pagination: Object,
		searchterm: String,
		sortBy: String,
	},
	data() {
		return {
			searching: Boolean(this.searchterm),
			term: this.searchterm,
		};
	},
	computed: {
		columns() {
			return {};
		},
		defaultSortBy() {
			return null;
		},
		empty() {
			return {};
		},
		sorting() {
			return (this.sortBy ?? this.defaultSortBy)?.split(" ") ?? [];
		},
	},
	created() {
		this.filter = this.$helper.debounce(
			() => this.reload({ searchterm: this.term || null }),
			200,
		);
	},
	methods: {
		onCell() {},
		onHeader({ columnIndex }) {
			// ascending, descending, then back to the default order
			const cycle = [
				...new Set([
					`${columnIndex} asc`,
					`${columnIndex} desc`,
					this.defaultSortBy,
				]),
			];
			const current = cycle.indexOf(this.sortBy ?? this.defaultSortBy);
			const next = cycle[(current + 1) % cycle.length];

			this.reload({ sortBy: next === this.defaultSortBy ? null : next });
		},
		async reload(query) {
			// core scrolls to the top on every new URL, the table should stay put
			const scroll = window.scrollY;

			// a new filter or order starts on the first page
			await this.$panel.reload({ query: { page: null, ...query } });
			await this.$nextTick();

			window.scrollTo(0, scroll);
		},
		async toggleSearch(forgiving = false) {
			if (forgiving && this.term) {
				this.term = null;
				this.filter();
				return;
			}

			if (this.searchterm) {
				this.reload({ searchterm: null });
			}

			this.term = null;
			this.searching = !this.searching;

			// without the input, the focus would fall back to the document
			if (this.searching === false) {
				await this.$nextTick();
				this.$refs.filter.focus();
			}
		},
	},
};
</script>

<style>
.k-retour-collection-view th.k-table-column {
	padding: 0;
}
.k-retour-collection-view th.k-table-column > button {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.k-retour-search.k-input {
	--input-color-back: var(--button-filled-color-back);
	--input-color-border: transparent;
	--input-height: var(--height-sm);
	--input-padding: var(--spacing-1) var(--spacing-2);
}
</style>
