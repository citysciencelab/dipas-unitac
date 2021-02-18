/**
 * @license GPL-2.0-or-later
 */

<script>
import CheckboxWithLabel from "../../../basicComponents/CheckboxWithLabel.vue";
import DipasButton from "../../../basicComponents/DipasButton.vue";

export default {
  name: "ContributionListFilterTermSelect",
  components: {
    CheckboxWithLabel,
    DipasButton
  },
  props: {
    headline: {
      type: String,
      default: ""
    },
    termTitle: {
      type: String,
      default: ""
    },
    termTitlePlural: {
      type: String,
      default: ""
    },
    terms: {
      type: Object,
      default () {
        return {};
      }
    },
    icon: {
      type: String,
      default: ""
    },
    value: {
      type: Array,
      default () {
        return [{}];
      }
    }
  },
  data () {
    return {
      showModal: false,
      intermediateSelection: this.value !== undefined ? this.value : [],
      selectedTerms: this.value !== undefined ? this.value : []
    };
  },
  computed: {
    selected () {
      return this.selectedTerms.length;
    },
    count () {
      return Object.keys(this.terms).length;
    }
  },
  watch: {
    selectedTerms (val) {
      this.$emit("input", val);
    },
    value (val) {
      this.intermediateSelection = val;
      this.selectedTerms = val;
    }
  },
  methods: {
    openSelection: function () {
      this.showModal = true;
    },
    cancel: function () {
      this.intermediateSelection = this.selectedTerms;
      this.showModal = false;
    },
    ok: function () {
      this.selectedTerms = this.intermediateSelection.sort();
      this.showModal = false;
    }
  }
};
</script>

<template>
  <div class="termselect">
    <div
      class="selectionOverview"
      @click="openSelection"
    >
      <p class="title">
        {{ termTitle }}
      </p>
      <p class="selection">
        <template v-if="selected">
          ({{ $t("ContributionList.ContributionListFilter.selection.selected", {'selected': selected, 'count': count}) }})
        </template>
        <template v-else>
          ({{ $t("ContributionList.ContributionListFilter.selection.all", {'termTitlePlural': termTitlePlural}) }})
        </template>
      </p>
    </div>

    <ModalElement
      v-if="showModal"
      class="selectionModal"
      :class="{selectionModalMobile: $root.isMobile}"
      @closeModal="cancel"
    >
      <p class="headline">
        {{ headline }}
      </p>

      <CheckboxWithLabel
        v-for="(term, val) in terms"
        :key="val"
        v-model="intermediateSelection"
        :label="term.label"
        :value="term.val"
        :icon="term[icon]"
      />

      <div
        v-if="!$root.isMobile"
        class="actions"
      >
        <DipasButton
          text="Abbrechen"
          class="grey angular"
          @click="cancel"
        />

        <DipasButton
          text="Auswahl übernehmen"
          class="blue angular"
          @click="ok"
        />
      </div>
      <div
        v-if="$root.isMobile"
        class="actionsMobile"
      >
        <DipasButton
          text="Auswahl übernehmen"
          class="blue angular mobileButton"
          @click="ok"
        />
        <DipasButton
          text="Abbrechen"
          class="grey angular mobileButton"
          @click="cancel"
        />
      </div>
    </ModalElement>
  </div>
</template>

<style>
    div.termselect div.selectionOverview {
        border: solid 2px #2B88D8;
        padding: 10px;
        cursor: pointer;
    }

    div.termselect div.selectionOverview p {
        display: inline-block;
        margin: 0;
        padding: 0;
    }

    div.termselect div.selectionOverview p.title {
        margin-right: 5px;
    }

    div.termselect div.selectionOverview p.selection {
        font-size: 12px;
        color: #2B88D8;
    }

    div.termselect div.selectionModal p.headline {
        font-size: 20px;
        font-weight: bold;
        color: black;
    }

    div.termselect div.selectionModal div.actions {
        margin-top: 80px;
    }

      div.termselect div.selectionModal div.actionsMobile {
        margin-top: 10px;
        margin-bottom: 20px;
    }

    div.termselect div.selectionModal div.actions button.dipasButton {
        display: inline-block;
        width: 200px;
        margin: 0;
    }

    div.termselect div.selectionModal div.actions button.dipasButton:first-child {
        margin-right: 20px;
    }

    div.termselect div.selectionModalMobile {
        top: 50px;
    }

    div.termselect div.selectionModal div.actionsMobile button.mobileButton {
        width:100%;
        margin-top: 20px;
    }
</style>
