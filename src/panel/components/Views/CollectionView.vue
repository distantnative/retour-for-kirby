<template>
	<k-retour-view v-bind="$props" class="k-retour-collection-view">
		<template #buttons>
			<k-search-input
				v-if="searching"
				:aria-label="$t('filter')"
				:autofocus="true"
				:placeholder="$t('filter') + ' …'"
				:value="q"
				class="k-input"
				@input="
					q = $event;
					pagination.page = 1;
				"
				@keydown.esc="toggleSearch(true)"
			/>

			<k-button
				ref="filter"
				:title="$t('filter')"
				icon="filter"
				size="sm"
				variant="filled"
				@click="toggleSearch()"
			/>
			<k-button
				v-for="(button, key) in buttons"
				v-bind="button"
				:key="key"
				size="sm"
				variant="filled"
			/>
		</template>

		<!-- Empty state -->
		<k-empty
			v-if="filteredItems.length === 0"
			v-bind="q ? { icon: 'search', text: $t('search.results.none') } : empty"
			layout="table"
		/>

		<!-- Table -->
		<k-table
			v-else
			:columns="columns"
			:index="index"
			:rows="paginatedItems"
			@cell="onCell"
			@header="onHeader"
		>
			<template #header="{ columnIndex, label }">
				<span>
					{{ label }}
					<k-icon
						v-if="columnIndex === sortBy"
						:type="sortDirection === 'asc' ? 'angle-up' : 'angle-down'"
					/>
				</span>
			</template>
			<template #options="{ row }">
				<k-options-dropdown :options="options(row)" />
			</template>
		</k-table>

		<footer class="k-bar k-collection-footer">
			<k-pagination
				v-bind="pagination"
				:details="true"
				:total="filteredItems.length"
				@paginate="pagination.page = $event.page"
			/>
		</footer>
	</k-retour-view>
</template>

<script>
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
		buttons() {
			return [];
		},
		columns() {
			return {};
		},
		empty() {
			return {};
		},
		filteredItems() {
			if (!this.q) {
				return this.data;
			}

			return this.$helper.array
				.search(this.searchIndex, this.q)
				.map((entry) => entry.row);
		},
		index() {
			return (this.pagination.page - 1) * this.pagination.limit + 1;
		},
		paginatedItems() {
			return this.sortedItems.slice(
				this.index - 1,
				this.pagination.limit * this.pagination.page,
			);
		},
		searchIndex() {
			// once per data change, not on every keystroke
			const keys = this.searchable;

			return this.data.map((row) => ({
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
					[...this.filteredItems],
					`${this.sortBy} ${this.sortDirection}`,
				);
			}

			return this.filteredItems;
		},
	},
	methods: {
		id(path) {
			// webservers often don't handle encoded slashes (see
			// https://github.com/distantnative/retour-for-kirby/issues/300),
			// so "/" is swapped for the invisible GROUP SEPARATOR (\x1D);
			// mirrors `Redirect::id()` in src/Retour/Redirect.php
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
.k-retour-collection-view .k-table-column {
	cursor: pointer;
}
.k-retour-collection-view th.k-table-column > span {
	display: inline-flex;
	width: 100%;
	align-items: center;
	justify-content: space-between;
}
.k-retour-collection-view .k-search-input {
	min-height: auto;
	height: var(--height-sm);
	--input-color-border: transparent;
	--input-color-back: light-dark(var(--color-gray-300), var(--color-gray-950));
}
</style>
