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

    <k-column width="1/1" v-if="false">
      <footer class="k-field-footer">
        <div data-theme="help" class="k-text k-field-help">
        <p>
          <b>{{ $t('retour.redirects') }}</b><br>
          {{ $t('retour.redirects.description') }}
        </p>
        <p>
          <b>{{ $t('retour.fails') }}</b><br>
          {{ $t('retour.fails.description') }}
        </p>
        </div>
      </footer>
    </k-column>

  </k-grid>
</template>

<script>

import Timeline from './../Charts/Timeline.vue';
import Share  from './../Charts/Share.vue';

export default {
  components: {
    timeline: Timeline,
    share: Share
  },
  data () {
    return {
      view:     'month',
      offset:   0,
      response: null
    }
  },
  computed: {
    api() {
      return 'retour/stats/' + this.view + '/' + this.offset;
    }
  },
  mounted() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$events.$emit('retour-load');
      this.$api.get(this.api).then(response => {
        this.response = response;
        this.$events.$emit('retour-loaded');
      });
    },
    prev() {
      this.offset -= 1;
      this.fetch();
    },
    next() {
      this.offset += 1;
      this.fetch();
    },
    show(view) {
      this.view   = view;
      this.offset = 0;
      this.fetch();
    }
  }
}
</script>
