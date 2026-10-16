<script>
import CollectionView from "./CollectionView.vue";

export default {
	extends: CollectionView,
	class: "k-retour-redirects-view",
	computed: {
		buttons() {
			return [
				{
					icon: "add",
					text: this.$t("add"),
					click: () => this.$panel.drawer.open("retour/redirects/create"),
				},
			];
		},
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
			};
		},
		searchable() {
			// the comment isn't shown, but helps to find a redirect
			return [...Object.keys(this.columns), "comment"];
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
					click: () =>
						this.$panel.drawer.open(
							`retour/redirects/${this.id(redirect.from)}/edit`,
						),
				},
				{
					text: this.$t("remove"),
					icon: "trash",
					click: () =>
						this.$panel.dialog.open(
							`retour/redirects/${this.id(redirect.from)}/delete`,
						),
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
