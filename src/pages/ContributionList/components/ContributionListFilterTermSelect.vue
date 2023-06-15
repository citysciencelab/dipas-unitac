/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The contribution list filter term select
 * @displayName ContributionListFilterTermSelect
 */
import CheckboxWithLabel from "../../../basicComponents/CheckboxWithLabel.vue";
import DipasButton from "../../../basicComponents/DipasButton.vue";

export default {
  name: "ContributionListFilterTermSelect",
  components: {
    CheckboxWithLabel,
    DipasButton
  },
  props: {
    /**
     * the headline
     */
    headline: {
      type: String,
      default: ""
    },
    /**
     * the term title
     */
    termTitle: {
      type: String,
      default: ""
    },
    /**
     * the term title in plural
     */
    termTitlePlural: {
      type: String,
      default: ""
    },
    /**
     * the terms
     */
    terms: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * the icon
     */
    icon: {
      type: String,
      default: ""
    },
    /**
     * value array
     */
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
    /**
     * holds the count number of selected terms
     * @returns {Number}
     */
    selected () {
      return this.selectedTerms.length;
    },
    /**
     * holds the count number
     * @returns {Number}
     */
    count () {
      return Object.keys(this.terms).length;
    }
  },
  watch: {
    /**
     * if selected terms changes
     * @returns {void}
     */
    selectedTerms (val) {
      /**
       * @event input
       */
      this.$emit("input", val);
    },
    /**
     * if value changes
     * @returns {void}
     */
    value (val) {
      this.intermediateSelection = val;
      this.selectedTerms = val;
    }
  },
  methods: {
    /**
     * shows the modal
     * @name openSelection
     * @returns {void}
     */
    openSelection: function () {
      this.showModal = true;
    },
    /**
     * cancel the selection and hides the modal
     * @name cancel
     * @returns {void}
     */
    cancel: function () {
      this.intermediateSelection = this.selectedTerms;
      this.showModal = false;
    },
    /**
     * set selectedTerms and hides the modal
     * @name ok
     * @returns {void}
     */
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
      @keyup.enter="openSelection"
    >
      <p class="title">
        {{ termTitle }}
      </p>
      <p
        class="selection"
        tabindex="0"
      >
        <template v-if="selected">
          ({{ $t("ContributionList.ContributionListFilter.selection.selected", {'selected': selected, 'count': count, 'termTitlePlural': termTitlePlural}) }})
        </template>
        <template v-else>
          ({{ $t("ContributionList.ContributionListFilter.selection.all", {'termTitlePlural': termTitlePlural}) }})
        </template>
      </p>
    </div>
    <!--
      @name ModalElement
      @fire closeModal
    -->
    <ModalElement
      v-if="showModal"
      class="selectionModal"
      :class="{selectionModalMobile: $root.isMobile}"
      @closeModal="cancel"
    >
      <h3 class="headline">
        {{ headline }}
      </h3>
      <fieldset>
        <legend class="sr-only">
          {{ headline }}
        </legend>
        <!--
          @name CheckboxWithLabel
          @property [Array] terms
        -->
        <CheckboxWithLabel
          v-for="(term, val) in terms"
          :key="val"
          v-model="intermediateSelection"
          :label="term.label"
          :value="term.val"
          :icon="term[icon]"
        />
      </fieldset>
      <div
        v-if="!$root.isMobile"
        class="actions"
      >
        <!--
          @name DipasButton
          @event click cancel
        -->
        <DipasButton
          :text="$t('ContributionList.ContributionListFilter.cancel')"
          class="grey angular"
          @click="cancel"
        />
        <!--
          @name DipasButton
          @event click ok
        -->
        <DipasButton
          :text="$t('ContributionList.ContributionListFilter.ok')"
          class="blue angular"
          @click="ok"
        />
      </div>
      <div
        v-if="$root.isMobile"
        class="actionsMobile"
      >
        <!--
          @name DipasButton
          @event click ok
        -->
        <DipasButton
          :text="$t('ContributionList.ContributionListFilter.ok')"
          class="blue angular mobileButton"
          @click="ok"
        />
        <!--
          @name DipasButton
          @event click cancel
        -->
        <DipasButton
          :text="$t('ContributionList.ContributionListFilter.cancel')"
          class="grey angular mobileButton"
          @click="cancel"
        />
      </div>
    </ModalElement>
  </div>
</template>

<style>
    div.termselect div.selectionOverview {
        border: solid 2px #005CA9;
        font-weight: bold;
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
        font-size: 0.75rem;
        font-weight: bold;
        color: #005CA9;
    }

    div.termselect div.selectionModal h3.headline {
        font-size: 1.25rem;
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
      width: auto;
      margin: 0;
      height: auto;
      padding: 10px 50px;
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
