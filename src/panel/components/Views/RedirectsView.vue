<script>
import CollectionView from "./CollectionView.vue";

export default {
	extends: CollectionView,
	data() {
		return {
			sortBy: null,
		};
	},
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
			return {
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
				hits: {
					label: this.$t("retour.hits"),
					width: "1/10",
					type: "count",
				},
			};
		},
		empty() {
			return {
				icon: "redirect",
				text: this.$t("retour.redirects.empty"),
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
