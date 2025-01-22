<template>
	<k-panel-inside class="k-retour-view">
		<k-header>
			{{ $t("view.retour") }}

			<template #buttons>
				<k-retour-timespan v-if="stats" :timespan="timespan" />
				<slot v-else name="buttons" />
			</template>
		</k-header>

		<template v-if="stats">
			<k-retour-stats :data="stats" :timespan="timespan" />

			<k-retour-tabs :tab="tab" :tabs="tabs">
				<slot name="buttons" />
			</k-retour-tabs>
		</template>

		<slot />
	</k-panel-inside>
</template>

<script>
export const props = {
	props: {
		data: [Object, Array],
		stats: [Boolean, Array],
		tab: String,
		tabs: Array,
		timespan: Object,
	},
};

export default {
	mixins: [props],
};
</script>

<style>
.k-retour-view .k-header:has(+ .k-retour-stats) {
	margin-bottom: 0;
	border-bottom: none;
}

@container (max-width: 30rem) {
	.k-retour-view .k-header {
		flex-direction: column;
	}
}
</style>
