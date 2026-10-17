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

			<div class="k-retour-tabs">
				<k-tabs :tab="tab" :tabs="tabs" />
				<k-button-group>
					<slot name="buttons" />
				</k-button-group>
			</div>
		</template>

		<slot />
	</k-panel-inside>
</template>

<script>
export const props = {
	props: {
		buttons: Array,
		data: Array,
		stats: Array,
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
	border-bottom-color: var(--panel-color-back);
}

.k-retour-tabs {
	margin-bottom: var(--spacing-6);
}
.k-retour-tabs .k-tabs {
	flex-grow: 1;
	margin-bottom: 0;
}
.k-retour-tabs > .k-button-group {
	flex-wrap: nowrap;
	margin-inline-start: auto;
}

@container (max-width: 30rem) {
	.k-retour-view .k-header {
		flex-direction: column;
	}
	.k-retour-tabs .k-tabs {
		margin-bottom: var(--spacing-6);
	}
	.k-retour-tabs > .k-button-group {
		justify-content: end;
	}
}
@container (min-width: 30rem) {
	.k-retour-tabs {
		display: flex;
		align-items: center;
	}
}
</style>
