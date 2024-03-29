<template>
	<div class="k-retour-tabs" :data-end="tabs.length < 2">
		<k-tabs :tab="tab" :tabs="tabs" />
		<k-button-group>
			<slot />
		</k-button-group>
	</div>
</template>

<script>
export default {
	props: {
		tab: String,
		tabs: Array,
	},
};
</script>

<style>
.k-retour-tabs {
	margin-bottom: var(--spacing-6);
}
.k-retour-tabs[data-end="true"] {
	justify-content: end;
}
.k-retour-tabs .k-tabs {
	flex-grow: 1;
	margin-bottom: 0;
}
.k-retour-tabs .k-tab-button:nth-child(1) .k-tabs-badge {
	--theme-color-back: var(--color-blue-300);
}
.k-retour-tabs .k-tab-button:nth-child(2) .k-tabs-badge {
	--theme-color-back: var(--color-red-300);
}

.k-retour-tabs > .k-button-group {
	flex-wrap: nowrap;
}

@container (max-width: 30rem) {
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
		justify-content: space-between;
		align-items: center;
	}
}
</style>
