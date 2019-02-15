<template>
  <k-grid gutter="medium">
    <k-column width="1/4">
      <share :data="stats.data" />
    </k-column>

    <k-column width="3/4">
      <timeline
        :frame="stats.frame"
        :offset="stats.offset"
        :data="stats.data"
        v-on="$listeners"
      />
    </k-column>
  </k-grid>
</template>

<script>
import Timeline from "./../Charts/Timeline.vue";
import Share  from "./../Charts/Share.vue";

export default {
  components: {
    timeline: Timeline,
    share: Share
  },
  props: {
    stats: Object
  }
}
</script>
