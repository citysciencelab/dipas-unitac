/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Holds and devides the planning contribution subareas
 * @displayName ContentPageParagraphDivisionInPlanningSubareas
 */
import _ from "underscore";
import ContentPageParagraphPlanningSubarea from "./ContentPageParagraphPlanningSubarea.vue";

export default {
  name: "ContentPageParagraphDivisionInPlanningSubareas",
  components: {
    ContentPageParagraphPlanningSubarea
  },
  props: {
    /**
     * holds the content object
     */
    content: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      uniqId: _.uniqueId("filter-"),
      filter: "all"
    };
  },
  computed: {
    /**
     * serves the subareas to filter
     * @returns {Array}
     */
    subareas () {
      return this.filter === "all"
        ? this.content.field_content
        : _.filter(this.content.field_content, element => element.field_name === this.filter);
    },

    /**
     * serves subarea options
     * @returns {Object} options
     */
    subareaOptions () {
      const options = {"all": this.$t("ParagraphDivisionInPlanningSubareas.Filter.optionAll")};

      this.content.field_content.forEach(element => {
        options[element.field_name] = element.field_name;
      });
      return options;
    },

    /**
     * serves wether more as one subareas available or not
     * @returns {Boolean}
     */
    showSubareaFilter () {
      return Object.keys(this.subareaOptions).length > 2;
    }
  }
};
</script>

<template>
  <div class="divisionInPlanningSubareas">
    <div
      v-if="showSubareaFilter"
      class="filter"
    >
      <label :for="uniqId">{{ $t('ParagraphDivisionInPlanningSubareas.Filter.label') }}:</label>

      <select
        :id="uniqId"
        v-model="filter"
      >
        <option
          v-for="(option, value) in subareaOptions"
          :key="value"
          :value="value"
        >
          {{ option }}
        </option>
      </select>
    </div>

    <div class="planningSubAreaConceptions">
      <!--
        ContentPageParagraphPlanningSubarea component
        @property {Array} subareas
      -->
      <ContentPageParagraphPlanningSubarea
        v-for="(element, index) in subareas"
        :key="index"
        :showHeadline="showSubareaFilter"
        :content="element"
      />
    </div>
  </div>
</template>

<style>
    div.divisionInPlanningSubareas {
        margin: 20px 0;
    }

    div.divisionInPlanningSubareas div.filter label {
        margin-right: 10px;
    }
</style>
