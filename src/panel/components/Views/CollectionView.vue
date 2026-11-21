<template>
	<k-retour-view
		v-bind="$props"
		:class="['k-retour-collection-view', $options.class]"
	>
		<template v-if="isSelecting" #buttons>
			<k-button
				v-for="button in batch"
				:key="button.icon"
				v-bind="button"
				:disabled="selected.length === 0"
				:responsive="true"
				:text="`${button.text} (${selected.length})`"
				size="sm"
				variant="filled"
			/>
			<k-button
				:responsive="true"
				:text="$t('cancel')"
				icon="cancel"
				size="sm"
				variant="filled"
				@click="toggleSelecting()"
			/>
		</template>
		<template v-else #buttons>
			<k-input
				v-if="searching"
				:aria-label="$t('filter')"
				:autofocus="true"
				:placeholder="$t('filter') + ' …'"
				:value="term"
				class="k-retour-search"
				type="search"
				@input="search"
				@keydown.esc.stop="term ? search(null) : toggleSearch()"
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
				v-if="batch.length > 0 && data.length > 0"
				:title="$t('select')"
				icon="checklist"
				size="sm"
				variant="filled"
				@click="toggleSelecting()"
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
			:selected="selected"
			:selecting="isSelecting"
			@cell="onCell"
			@header="onHeader"
			@select="selected = $event"
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
			isSelecting: false,
			searching: Boolean(this.searchterm),
			selected: [],
			term: this.searchterm,
		};
	},
	computed: {
		/**
		 * Buttons for the selected rows
		 */
		batch() {
			return [];
		},
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
	watch: {
		// the rows of the selection may be gone, e.g. after removing them
		data() {
			this.selected = [];
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
			await this.$panel.reload({ query: { page: null, ...query } });
			await this.$nextTick();

			window.scrollTo(0, scroll);
		},
		search(term) {
			this.term = term;
			this.filter();
		},
		toggleSelecting() {
			this.isSelecting = !this.isSelecting;
			this.selected = [];
		},
		async toggleSearch() {
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
