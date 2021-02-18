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
     */
    value: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * Provides the boolean whether you use rubrics in contribution.
     * @values true, false
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
     * computed ToDo
     */
    rubrics () {
      return this.$store.getters.allRubrics;
    },
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
  <div class="createContributionStep1">
    <p class="headline">
      {{ $t("CreateContributionModal.StepType.headline") }}
    </p>

    <RadioGroup
      v-if="useRubrics"
      v-model="rubric"
      :options="rubricOptions"
      :value="rubric"
    />
    <div v-else>
      {{ $t("CreateContributionModal.StepType.no_rubrics_text") }}
    </div>
  </div>
</template>
