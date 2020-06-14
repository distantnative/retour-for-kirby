<template>
  <span
    v-if="row.hits > 0"
    :title="$t('retour.hits.last') + ': ' + row.last"
    class="flex justify-between"
  >
    <k-button
      :icon="{
        type: 'clock',
        size: 'small',
        color: 'gray-light'
      }"
      :tooltip="$t('retour.hits.last') + ': ' + row.last"
      class="cursor-default"
    />
    {{ row.hits }}
  </span>
</template>

<script>
export default {
  props: {
    row: Object
  }
}
</script>
