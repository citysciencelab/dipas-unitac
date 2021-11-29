/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for CreateContributionModalStepContentsStep3 is described here.
 * @displayName CreateContributionModalStepContentsStep3
 */
import RadioGroup from "./RadioGroup.vue";

export default {
  /**
   * More Informations for CreateContributionModalStepContentsStep3 is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CreateContributionModalStepContentsStep3",
  components: {
    RadioGroup
  },
  props: {
    /**
     * The content object of the actual step
     * @name value
     */
    value: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * Provides the boolean whether you use rubrics in contribution.
     * @name useRubrics
     */
    useRubrics: {
      type: Boolean
    }
  },
  data () {
    return {
      rubric: this.value.rubric
    };
  },
  computed: {
    /**
     * serves the rubrics
     * @returns {Object} rubrics
     */
    rubrics () {
      return this.$store.getters.allRubrics;
    },
    /**
     * serves the rubrics options
     * @returns {Object} rubric options
     */
    rubricOptions () {
      const options = {};

      this.rubrics.forEach((rubData, index) => {
        options[index] = {
          val: parseInt(rubData.id, 10),
          label: rubData.name
        };
      });
      return options;
    }
  },
  watch: {
    /**
     * Saves the choosen rubric
     * @event input
     * @param {String} rubric which rubric is choosen
     */
    rubric (val) {
      this.$emit("input", {rubric: val});
    }
  }
};
</script>

<template>
  <div class="createContributionStep3">
    <h3 class="headline">
      {{ $t("CreateContributionModal.StepType.headline") }}
    </h3>
    <fieldset>
      <legend class="sr-only">
        {{ $t("CreateContributionModal.StepType.headline") }}
      </legend>

      <RadioGroup
        v-if="useRubrics"
        v-model="rubric"
        :options="rubricOptions"
        :value="rubric"
      />
      <div v-else>
        {{ $t("CreateContributionModal.StepType.no_rubrics_text") }}
      </div>
    </fieldset>
  </div>
</template>

<style>
    div.createContributionStep3 div.radiogroup label.withIcon {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 95%;
    }

    #app.mobile div.createContributionStep2 div.radiogroup {
        padding-bottom: 25%;
    }

    div.createContributionStep3 div.radio-wrapper {
        margin-bottom: 0;
        height: 2rem;
    }

    div.createContributionStep3 div.radio-wrapper label:before {
        margin-bottom: 2px;
    }

    div.createContributionStep3 div.radio-wrapper input:focus-visible + label {
        outline: 3px solid #005CA9;
        outline-offset: 5px;
        opacity: 1;
    }
  </style>
