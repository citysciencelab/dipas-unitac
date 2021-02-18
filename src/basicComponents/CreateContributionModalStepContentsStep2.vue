/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * More Informations for CreateContributionModalStepContentsStep2 is described here.
 * @displayName CreateContributionModalStepContentsStep2
 */
import KeywordSelector from "./KeywordSelector";
import RadioGroup from "./RadioGroup.vue";

export default {
  /**
   * More Informations for CreateContributionModalStepContentsStep2 is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "CreateContributionModalStepContentsStep2",
  components: {
    RadioGroup,
    KeywordSelector
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
    }
  },
  data () {
    return {
      category: this.value.category
    };
  },
  computed: {
    /**
     * computed ToDo
     */
    categories () {
      return this.$store.getters.allCategories;
    },
    categoryOptions () {
      const options = {};

      this.categories.forEach((catData, index) => {
        options[index] = {
          val: parseInt(catData.id, 10),
          label: catData.name
        };
      });
      return options;
    },
    categoryIcons () {
      const icons = {};

      this.categories.forEach((catData, index) => {
        icons[index] = {
          val: parseInt(catData.id, 10),
          icon: catData.field_category_icon
        };
      });
      return icons;
    }
  },
  watch: {
    category (val) {
      this.$emit("input", Object.assign(this.value, {category: val}));
    }
  }
};
</script>

<template>
  <div class="createContributionStep1">
    <KeywordSelector
      v-if="$store.getters.isKeywordServiceEnabled"
      :value="value"
    />
    <p class="headline">
      {{ $t("CreateContributionModal.StepCategory.headline") }}
    </p>
    <RadioGroup
      v-model="category"
      :options="categoryOptions"
      :icons="categoryIcons"
      :value="category"
    />
  </div>
</template>
