<script>
import CollectionView from "./CollectionView.vue";

export default {
	extends: CollectionView,
	class: "k-retour-redirects-view",
	computed: {
		columns() {
			const multilang = this.$panel.multilang;
			const columns = {
				from: {
					label: this.$t("retour.redirects.from"),
					type: "path",
					width: multilang ? "3/10" : "7/20",
					mobile: true,
				},
				to: {
					label: this.$t("retour.redirects.to"),
					type: "link",
					width: multilang ? "3/10" : "7/20",
				},
				...(multilang && {
					language: {
						label: this.$t("language"),
						width: "1/10",
					},
				}),
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
			this.$panel.drawer.open(`retour/redirects/${row.id}/edit`, {
				query: {
					column: columnIndex,
				},
			});
		},
	},
};
</script>

<style>
.k-retour-redirects-view td.k-table-column {
	cursor: pointer;
}
</style>
