/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The component holds all step forms.
 * @displayName CreateContributionModalStepContents
 */
import CreateContributionModalStepContentsStep1 from "./CreateContributionModalStepContentsStep1.vue";
import CreateContributionModalStepContentsStep2 from "./CreateContributionModalStepContentsStep2.vue";
import CreateContributionModalStepContentsStep3 from "./CreateContributionModalStepContentsStep3.vue";
import CreateContributionModalStepContentsStep4 from "./CreateContributionModalStepContentsStep4.vue";
import CreateContributionModalStepContentsStep5 from "./CreateContributionModalStepContentsStep5.vue";

export default {
  /**
   * More Informations for CreateContributionModaStepContents is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CreateContributionModalStepContents",
  components: {
    CreateContributionModalStepContentsStep1,
    CreateContributionModalStepContentsStep2,
    CreateContributionModalStepContentsStep3,
    CreateContributionModalStepContentsStep4,
    CreateContributionModalStepContentsStep5
  },
  props: {
    /**
     * Value holds the form content as an object.
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
    },
    /**
     * Active step number at the process of the wizard.
     */
    activeStep: {
      type: Number,
      default: 1
    }
  },
  computed: {
    component () {
      return "CreateContributionModalStepContentsStep" + this.activeStep;
    }
  }
};
</script>

<template>
  <div class="createContributionStep">
    <!--
      triggered on change
      @event input
      @event jumpTo
    -->
    <component
      :is="component"
      :key="component.name"
      :value="value"
      :useRubrics="useRubrics"
      @jumpTo="$emit('jumpTo', $event)"
      @input="$emit('input', $event)"
    />
  </div>
</template>

<style>
    div.createContributionStep {
        width: 100%;
        min-height: 50vh;
        margin-top: 30px;
    }

    #app.mobile div.createContributionStep {
        min-height: auto;
    }

    div.createContributionStep h3.headline {
        font-size: 1.5rem;
        font-weight: bold;
        color: black;
        margin: 0 0 10px 0;
    }

    div.createContributionStep label {
        font-size: 1rem;
        font-weight: bold;
        color: black;
        margin: 5px 0 5px 0;
    }

    div.createContributionStep input,
    div.createContributionStep textarea {
        padding: 5px;
    }

    div.createContributionStep input:focus:not(:focus-visible) ,
    div.createContributionStep textarea:focus:not(:focus-visible)  {
        outline: none;
    }
</style>

