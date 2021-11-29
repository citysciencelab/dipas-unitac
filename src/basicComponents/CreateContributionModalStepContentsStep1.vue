/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Component that provides the content of the first step at wizard.
 * @displayName CreateContributionModalStepContentsStep1
 */
import _ from "underscore";

export default {
  /**
   * More Informations for CreateContributionModalStepContentsStep1 is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CreateContributionModalStepContentsStep1",
  props: {
    /**
     * The content object of the actual step
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
      uniqueIdHeadline: _.uniqueId("title-"),
      uniqueIdText: _.uniqueId("text-"),
      headline: this.value.headline,
      text: this.value.text,
      headlineMinLength: this.value.headlineMinLength,
      contributionMinLength: this.value.contributionMinLength
    };
  },
  computed: {
    /**
     * serves the max length of contribution
     * @returns {Number} length
     */
    maxlength () {
      return this.$store.getters.contributionMaxlength;
    },
    /**
     * serves the remaining chars for text length
     * @returns {Number} length
     */
    remaining () {
      return this.maxlength - this.text.length;
    },
    headlineTooShort () {
      return this.headline.length < this.headlineMinLength;
    },
    contributionTooShort () {
      return this.text.length < this.contributionMinLength;
    }
  },
  watch: {
    /**
     * @event input if the headline or text changing
     */
    headline () {
      this.$emit("input", {headline: this.headline, text: this.text});
    },
    /**
     * @event input if the headline or text changing
     */
    text () {
      this.$emit("input", {headline: this.headline, text: this.text});
    }
  }
};
</script>

<template>
  <div class="createContributionStep1">
    <h3 class="headline">
      {{ $t("CreateContributionModal.StepContents.headline") }}
    </h3>

    <label
      v-if="headlineTooShort"
      :for="uniqueIdHeadline"
    >
      {{ $t("CreateContributionModal.StepContents.title") }} ({{ $t("CreateContributionModal.StepContents.minimalChars", {"minChar": headlineMinLength}) }})
    </label>
    <label
      v-else
      :for="uniqueIdHeadline"
    >
      {{ $t("CreateContributionModal.StepContents.title") }}
    </label>

    <input
      :id="uniqueIdHeadline"
      v-model="headline"
      type="text"
      class="input"
      aria-describedby="minimumchars"
      maxlength="200"
      autocomplete="off"
    />

    <label
      v-if="contributionTooShort"
      :for="uniqueIdText"
    >
      {{ $t("CreateContributionModal.StepContents.text") }} ({{ $t("CreateContributionModal.StepContents.minimalChars", {"minChar": contributionMinLength}) }})
    </label>
    <label
      v-else
      :for="uniqueIdText"
    >
      {{ $t("CreateContributionModal.StepContents.text") }}
    </label>

    <textarea
      :id="uniqueIdText"
      v-model="text"
      aria-describedby="charsremaining minimumchars"
      class="input"
      :maxlength="maxlength"
      autocomplete="off"
    />
    <p
      id="charsremaining"
      class="charCounter"
    >
      {{ $t("CreateContributionModal.StepContents.remainingChars", {"remaining": remaining, "maxlength": maxlength}) }}
    </p>
  </div>
</template>

<style>
    div.createContributionStep1 .input {
        display: block;
        width: 100%;
        border: solid 1px #767676;
    }

    div.createContributionStep1 textarea {
        height: 250px;
        white-space: pre-wrap;
    }

    #app.mobile div.createContributionStep1 textarea {
        height: 100px;
    }

    div.createContributionStep1 p.charCounter {
        font-size: 0.8rem;
        font-weight: normal;
        color: #595959;
        margin: 5px 0 0 0;
        padding: 0 0 5px 0;
    }

    #app.mobile div.customModal.createContributionModal.modalMobile div.modalContent {
        height: calc(var(--vh, 1vh) * 100);
        overflow-y: auto;
    }
</style>
