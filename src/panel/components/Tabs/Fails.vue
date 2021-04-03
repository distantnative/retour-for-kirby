<template>
  <list
    :empty="$t('retour.failures.empty')"
    :columns="columns"
    :rows="rows"
    :options="options"
    type="failures"
    @option="onOption"
  >
    <template v-if="canUpdate" #button>
      <k-button
        icon="trash"
        @click="$refs.flush.open()"
      >
        {{ $t('retour.failures.clear') }}
      </k-button>
    </template>

    <template #dialogs>
      <!-- remove dialog -->
      <k-remove-dialog
        ref="remove"
        :submit-button="$t('delete')"
        @submit="remove"
      >
        <k-text>{{ $t('field.structure.delete.confirm') }}</k-text>
      </k-remove-dialog>

      <!-- flush dialog -->
      <k-remove-dialog
        ref="flush"
        :submit-button="$t('retour.failures.clear')"
        @submit="flush"
      >
        <k-text>{{ $t('retour.failures.clear.confirm') }}</k-text>
      </k-remove-dialog>
    </template>

  </list>
</template>

<script>
import permissions from "../../mixins/permissions.js";
import List from "../List/List.vue";

export default {
  mixins: [permissions],
  components: {
    List
  },
  data() {
    return {
      row: null
    };
  },
  computed: {
    columns() {
      return {
        path: {
          label: this.$t("retour.failures.path"),
          type: "link",
          filter: true,
          width: "1/3"
        },
        referrer: {
          label: this.$t("retour.failures.referrer"),
          type: "link",
          filter: true,
          width: "1/3"
        },
        hits: {
          label: this.$t("retour.hits"),
          type: "count",
          width: "1/12",
          align: "right"
        }
      };
    },
    options() {
      if (this.canUpdate === false) {
        return false;
      }

      return [
        {
          text: this.$t("retour.failures.resolve"),
          icon: "add",
          click: "resolve"
        },
        {
          text: this.$t("remove"),
          icon: "trash",
          click: "remove"
        }
      ];
    },
    rows() {
      return Object.values(this.$store.state.retour.data.fails);
    }
  },
  methods: {
    async flush() {
      try {
        await this.$api.post("retour/log/flush");
        this.$refs.flush.close();
        await this.$store.dispatch("retour/data");
        this.$store.dispatch("notification/success");

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async onOption(option, row) {
      switch (option) {
        case "remove":
          this.row = row;
          this.$refs.remove.open();
          break;
        case "resolve":
          await this.$router.push("#routes");
          this.$events.$emit("retour.resolve", { from: row.path });
          break;
      }
    },
    async remove() {
      await this.$api.delete("retour/fails", {
        path: this.row.path,
        referrer: this.row.referrer
      });
      this.$refs.remove.close();
      await this.$store.dispatch("retour/data");
      this.$store.dispatch("notification/success");
      this.row = null;
    }
  }
}
</script>
