<template>
  <div
    v-if="row.hits > 0"
    :title="$t('retour.hits.last') + ': ' + row.last"
    class="flex justify-between px-2"
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
  </div>
</template>

<script>
export default {
  props: {
    row: Object
  }
}
</script>
