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
				:value="q"
				class="k-retour-search"
				type="search"
				@input="
					q = $event;
					pagination.page = 1;
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
			v-if="filteredItems.length === 0"
			v-bind="q ? { icon: 'search', text: $t('search.results.none') } : empty"
			layout="table"
		/>

		<k-table
			v-else
			:columns="columns"
			:index="index"
			:rows="paginatedItems"
			@cell="onCell"
			@header="onHeader"
		>
			<template #header="{ columnIndex, label }">
				<button type="button">
					{{ label }}
					<k-icon
						v-if="columnIndex === sortBy"
						:type="sortDirection === 'asc' ? 'angle-up' : 'angle-down'"
					/>
				</button>
			</template>
			<template #options="{ row }">
				<k-options-dropdown :options="options(row)" />
			</template>
		</k-table>

		<footer class="k-bar k-collection-footer">
			<k-pagination
				:details="true"
				:limit="pagination.limit"
				:page="page"
				:total="filteredItems.length"
				@paginate="pagination.page = $event.page"
			/>
		</footer>
	</k-retour-view>
</template>

<script>
import { toRaw } from "vue";
import { props as View } from "./View.vue";

export default {
	mixins: [View],
	data() {
		return {
			searching: false,
			q: null,
			sortBy: null,
			sortDirection: "asc",
			pagination: {
				page: 1,
				limit: 20,
			},
		};
	},
	computed: {
		columns() {
			return {};
		},
		empty() {
			return {};
		},
		filteredItems() {
			if (!this.q) {
				return this.sortedItems;
			}

			// the index is sorted already, typing doesn't sort again
			return this.$helper.array
				.search(this.searchIndex, this.q)
				.map((entry) => entry.row);
		},
		index() {
			return (this.page - 1) * this.pagination.limit + 1;
		},
		page() {
			// the view keeps its page across reloads, e.g. after
			// deleting a row or for a timespan with fewer rows
			const pages = Math.ceil(
				this.filteredItems.length / this.pagination.limit,
			);
			return Math.max(1, Math.min(this.pagination.page, pages));
		},
		paginatedItems() {
			return this.filteredItems.slice(
				this.index - 1,
				this.pagination.limit * this.page,
			);
		},
		rows() {
			// rows are only replaced on reload, never changed,
			// so they don't need a reactive proxy each
			return toRaw(this.data);
		},
		searchIndex() {
			// once per data or sort change, not on every keystroke
			const keys = this.searchable;

			return this.sortedItems.map((row) => ({
				row,
				text: keys
					.map((key) => row[key])
					.filter((value) => ["string", "number"].includes(typeof value))
					.join("\n"),
			}));
		},
		searchable() {
			return Object.keys(this.columns);
		},
		sortedItems() {
			if (this.sortBy) {
				// sortBy() sorts in place, a shallow copy is
				// enough to keep the original order intact
				return this.$helper.array.sortBy(
					[...this.rows],
					`${this.sortBy} ${this.sortDirection}`,
				);
			}

			return this.rows;
		},
	},
	watch: {
		page(page) {
			// don't jump back to the old page when rows grow again
			this.pagination.page = page;
		},
	},
	methods: {
		id(path) {
			// see Redirect::id()
			return encodeURIComponent(path.replace(/\//g, "\x1D"));
		},
		onCell() {},
		onHeader({ columnIndex }) {
			if (this.sortBy === columnIndex) {
				if (this.sortDirection === "desc") {
					columnIndex = null;
				} else {
					this.sortDirection = "desc";
				}
			} else {
				this.sortDirection = "asc";
			}

			this.sortBy = columnIndex;
			this.pagination.page = 1;
		},
		options() {
			return [];
		},
		async toggleSearch(forgiving = false) {
			if (forgiving && this.q) {
				this.q = null;
				return;
			}

			this.q = null;
			this.searching = !this.searching;

			// the input focuses itself, but when it's gone,
			// the focus would fall back to the document
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
