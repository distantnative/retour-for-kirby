<template>
  <k-grid gutter="medium">
    <k-column width="1/4">
      <share :response="response" />
    </k-column>

    <k-column width="3/4">
      <timeline
        :response="response"
        :view="view"
        :offset="offset"
        @show="show"
        @prev="prev"
        @next="next"
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
  data () {
    return {
      view: "month",
      offset: 0,
      response: null
    }
  },
  computed: {
    api() {
      return "retour/stats/" + this.view + "/" + this.offset;
    }
  },
  mounted() {
    this.fetch();
  },
  methods: {
    fetch(before = () => {}) {
      this.$store.dispatch("isLoading", true);
      before();
      this.$api.get(this.api).then(response => {
        this.response = response;
        this.$store.dispatch("isLoading", false);
      });
    },
    prev() {
      this.fetch(() => {
        this.offset -= 1;
      });
    },
    next() {
      this.fetch(() => {
        this.offset += 1;
      });
    },
    show(view) {
      this.fetch(() => {
        this.offset = 0;
        this.view = view;
      });
    }
  }
}
</script>
