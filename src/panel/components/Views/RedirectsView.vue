<script>
import CollectionView from "./CollectionView.vue";

export default {
	extends: CollectionView,
	class: "k-retour-redirects-view",
	computed: {
		columns() {
			const columns = {
				from: {
					label: this.$t("retour.redirects.from"),
					type: "path",
					width: "7/20",
					mobile: true,
				},
				to: {
					label: this.$t("retour.redirects.to"),
					type: "link",
					width: "7/20",
				},
				status: {
					label: this.$t("status"),
					type: "status",
					width: "1/10",
				},
				priority: {
					label: this.$t("retour.redirects.priority.abbr"),
					type: "priority",
					width: "1/10",
				},
			};

			// hits are only counted with logs enabled
			if (this.stats) {
				columns.hits = {
					label: this.$t("retour.hits"),
					width: "1/10",
					type: "count",
				};
			}

			return columns;
		},
		empty() {
			return {
				icon: "redirect",
				text: this.$t("retour.redirects.empty"),
				onClick: () => this.$panel.drawer.open("retour/redirects/create"),
			};
		},
	},
	methods: {
		onCell({ row, columnIndex }) {
			this.$panel.drawer.open(`retour/redirects/${this.id(row.from)}/edit`, {
				query: {
					column: columnIndex,
				},
			});
		},
		options(redirect) {
			return [
				{
					text: this.$t("edit"),
					icon: "edit",
					drawer: `retour/redirects/${this.id(redirect.from)}/edit`,
				},
				{
					text: this.$t("remove"),
					icon: "trash",
					dialog: `retour/redirects/${this.id(redirect.from)}/delete`,
				},
			];
		},
	},
};
</script>

<style>
.k-retour-redirects-view td.k-table-column {
	cursor: pointer;
}
</style>
