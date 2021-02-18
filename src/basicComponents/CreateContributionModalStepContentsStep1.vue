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
      text: this.value.text
    };
  },
  computed: {
    /**
     * computed ToDo
     */
    maxlength () {
      return this.$store.getters.contributionMaxlength;
    },
    remaining () {
      return this.maxlength - this.text.length;
    }
  },
  watch: {
    headline () {
      this.$emit("input", {headline: this.headline, text: this.text});
    },
    text () {
      this.$emit("input", {headline: this.headline, text: this.text});
    }
  }
};
</script>

<template>
  <div class="createContributionStep1">
    <p class="headline">
      {{ $t("CreateContributionModal.StepContents.headline") }}
    </p>

    <label :for="uniqueIdHeadline">{{ $t("CreateContributionModal.StepContents.title") }}</label>

    <input
      :id="uniqueIdHeadline"
      v-model="headline"
      type="text"
      class="input"
      maxlength="200"
      autocomplete="off"
    />

    <label :for="uniqueIdText">{{ $t("CreateContributionModal.StepContents.text") }}</label>

    <textarea
      :id="uniqueIdText"
      v-model="text"
      class="input"
      :maxlength="maxlength"
      autocomplete="off"
    />

    <p class="charCounter">
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
        font-size: 14px;
        font-weight: normal;
        color: #767676;
        margin: 5px 0 0 0;
        padding: 0 0 5px 0;
    }

    div.createContributionStep1 div.radiogroup label.withIcon {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 95%;
    }

    #app.mobile div.createContributionStep1 div.radiogroup {
        padding-bottom: 25%;
    }

    div.createContributionStep1 div.radio-wrapper {
        margin-bottom: 0;
        height: 37px;
    }

    div.createContributionStep1 div.radio-wrapper label:before {
        margin-bottom: 2px;
    }

    #app.mobile div.customModal.createContributionModal.modalMobile div.modalContent {
        height: calc(var(--vh, 1vh) * 100);
        overflow-y: auto;
    }
</style>
