/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for CreateContributionModalStepIndicator is described here.
 * @displayName CreateContributionModalStepIndicator
 */
export default {
  /**
   * More Informations for CreateContributionModalStepIndicator is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CreateContributionModalStepIndicator",
  props: {
    /**
     * The content object of the actual step
     */
    activeStep: {
      type: Number,
      default: 0
    }
  },
  data () {
    return {
      steps: {
        1: this.$t("CreateContributionModal.StepIndicator.content"),
        2: this.$t("CreateContributionModal.StepIndicator.category"),
        3: this.$t("CreateContributionModal.StepIndicator.type"),
        4: this.$t("CreateContributionModal.StepIndicator.map"),
        5: this.$t("CreateContributionModal.StepIndicator.overview")
      }
    };
  }
};
</script>

<template>
  <ul class="createContributionStepIndicator">
    <li
      v-for="(step, index) in steps"
      :key="'createContributionStep-' + index"
      :class="[activeStep === index ? 'red' : (activeStep > index ? 'blue jumpButton' : 'grey')]"
      @click="activeStep > index ? $emit('jumpTo', index) : null"
    >
      <span class="content">{{ step }}</span>
    </li>
  </ul>
</template>

<style>
    ul.createContributionStepIndicator {
        list-style-image: none;
        list-style-type: none;
        list-style-position: outside;
        margin: 0;
        padding: 0;
        white-space: nowrap;
    }

    ul.createContributionStepIndicator li {
        display: inline-block;
        width: 19.5%;
        border-bottom-style: solid;
        border-bottom-width: 8px;
        margin: 0 2px 0 0;
        padding: 0 0 5px 0;
        font-size: 13px;
        font-weight: bold;
        cursor: default;
    }

    #app.mobile ul.createContributionStepIndicator li span.content {
        display: none;
    }

    ul.createContributionStepIndicator li.jumpButton {
        cursor: pointer;
    }

    ul.createContributionStepIndicator li:last-child {
        margin-right: 0;
    }

    ul.createContributionStepIndicator li.grey {
        border-bottom-color: #9EAAB7;
        color: #003063;
    }

    ul.createContributionStepIndicator li.red {
        border-bottom-color: #E10019;
        color: #E10019;
    }

    ul.createContributionStepIndicator li.blue {
        border-bottom-color: #003063;
        color: #003063;
    }
</style>
