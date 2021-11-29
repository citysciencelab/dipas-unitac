/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Shows Keywords an let the user remove keywords and lets add own ones.
 * @displayName KeywordSelector
 */
import _ from "underscore";
export default {
  /**
   * More Informations for the KeywordSelector is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "KeywordSelector",
  props: {
    /**
     * holds the keywords data object
     * @name value
     */
    value: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      /**
       * Single String holds the custom Keyword
       * @values String
       */
      ownKeyword: "",
      keywordListTimestamp: "keywordList-" + window.performance.now()
    };
  },
  computed: {
    /**
     * serves the proposal keywords
     * @name proposals
     * @return {Array} proposals
     */
    proposals () {
      return this.value.proposals;
    },
    /**
     * serves the value wether proposals are available or not
     * @name hasProposals
     * @return {Boolean} hasProposals
     */
    hasProposals () {
      return !_.isUndefined(this.proposals) && _.isArray(this.proposals) && this.proposals.length;
    },
    /**
     * serves the value how many abandoned proposals are in memory
     * @name hasAbandonedProposals
     * @return {Number} hasProposals
     */
    hasAbandonedProposals () {
      return this.value.abandonedProposals.length;
    }
  },
  watch: {
    /**
     * clone selectedKeywords for later use
     * @returns {void}
     */
    proposals (val) {
      this.value.selectedKeywords = _.clone(val);
    }
  },
  methods: {
    /**
     * remove a keyword from List and sorts back to proposals if it is a proposal
     * @param {String} keyword Keyword
     * @returns {void}
     */
    removeKeyword: function (keyword) {
      const selectedKeywords = _.clone(this.value.selectedKeywords);

      if (this.value.proposals.indexOf(keyword) !== -1) {
        this.value.abandonedProposals.push(keyword);
      }
      selectedKeywords.splice(selectedKeywords.indexOf(keyword), 1);
      this.value.selectedKeywords = selectedKeywords;
      this.keywordListTimestamp = "keywordList-" + window.performance.now();
    },
    /**
     * remove a keyword from List and sorts back to proposals if it is a proposal
     * @returns {void}
     */
    keyUpToAddCustomKeyword: function () {
      const trimmed = this.ownKeyword.trim();

      if (trimmed.length !== 0) {
        this.ownKeyword = "";
        this.value.selectedKeywords.push(trimmed);
      }
    },
    /**
     * @param {String} keyword which sets the single proposal
     * @returns {void}
     */
    addProposal: function (keyword) {
      this.value.selectedKeywords.push(keyword);
      this.value.abandonedProposals.splice(this.value.abandonedProposals.indexOf(keyword), 1);
    }
  }
};
</script>

<template>
  <div>
    <p class="headline">
      {{ $t("CreateContributionModal.StepCategory.KeywordSelector.headline") }}
    </p>

    <p>{{ $t("CreateContributionModal.StepCategory.KeywordSelector.note") }}</p>

    <div class="component-container">
      <div
        :key="keywordListTimestamp"
        class="keywords-container"
      >
        <transition-group name="list">
          <span
            v-for="keyword in value.selectedKeywords"
            :key="keyword"
            class="selected-keywords"
          >
            {{ keyword }}
            <i
              class="material-icons keyword-icon-style"
              @click="removeKeyword(keyword)"
            >close
            </i>
          </span>
        </transition-group>

        <transition
          name="list"
          tag="input"
        >
          <input
            v-model="ownKeyword"
            type="textfield"
            class="keyword-input"
            :placeholder="$t('CreateContributionModal.StepCategory.KeywordSelector.placeholder')"
            @keyup.enter="keyUpToAddCustomKeyword"
          />
        </transition>
      </div>

      <transition name="proposalLabel">
        <div v-if="hasAbandonedProposals">
          <p class="proposals">
            {{ $t("CreateContributionModal.StepCategory.KeywordSelector.proposals") }}
          </p>
        </div>
      </transition>

      <transition-group name="proposals">
        <p
          v-for="proposal in value.abandonedProposals"
          :key="proposal"
          class="proposal-keywords"
        >
          {{ proposal }}
          <i
            class="material-icons keyword-icon-style"
            @click="addProposal(proposal)"
          >
            add
          </i>
        </p>
      </transition-group>

      <p />
    </div>
  </div>
</template>

<style>
.keyword-input {
    margin-top: 1px;
    padding-left: 20px !important;
    padding-bottom: 8px !important;
    height: 26px;
    border-radius: 15px;
    border: 0.5px solid #cccccc;
}
.proposals {
    float: left;
    padding-right: 20px;
}
.proposal-keywords {
    float: left;
    color: #ffffff;
    background-color: rgb(121, 121, 121);
    margin: 1px 3px 1px 0px;
    padding: 0px 30px 0px 20px;
    border-radius: 15px;
}
.keyword-icon-style {
    position: absolute;
    cursor: pointer;
}
.component-container {
    height: 157px;
    padding-left: 0px;
}
.component-container p {
    clear: both;
}
.keywords-container {
    min-height: 71px;
    background-color: #efefef;
    border: 1px solid #dedede;
    border-radius: 2px;
    padding: 4px 4px 4px 4px;
    margin-bottom: 20px;
    min-width: 100%
}
.selected-keywords {
    float: left;
    color: #ffffff;
    background-color: #005CA9;
    margin: 1px 3px 6px 0px;
    padding: 0px 30px 1px 20px;
    border-radius: 15px;
}
.keyword-icon-style:hover {
    background-color: rgb(0, 125, 228);
    border-radius: 4px;
}
.keyword-selector {
    padding-bottom: 30px;
}
.list-enter-active, .list-leave-active {
  transition: all 0.1s;
}
.list-enter, .list-leave-to /* .list-leave-active below version 2.1.8 */ {
  opacity: 0;
  transform: translateY(15px);
}
.proposalLabel-enter-active, .proposalLabel-leave-active {
  transition: all 0.1s;
}
.proposalLabel-enter, .proposalLabel-leave-to /* .list-leave-active below version 2.1.8 */ {
  opacity: 0;
  transform: translateY(-15px);
}
.proposals-enter-active, .proposals-leave-active {
  transition: all 0.1s;
}
.proposals-enter, .proposals-leave-to /* .list-leave-active below version 2.1.8 */ {
  opacity: 0;
  transform: translateY(-15px);
}
</style>

