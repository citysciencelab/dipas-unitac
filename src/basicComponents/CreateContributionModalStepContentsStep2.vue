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
     * @name categories
     * @returns {Object} all categories
     */
    categories () {
      return this.$store.getters.allCategories;
    },
    /**
     * @name categoryOptions
     * @returns {Object} options
     */
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
    /**
     * @name categoryIcons
     * @returns {Object} icons
     */
    categoryIcons () {
      const icons = {};

      this.categories.forEach((catData, index) => {
        icons[index] = {
          val: parseInt(catData.id, 10),
          icon: catData.field_category_icon
        };
      });
      return icons;
    },
    categoryCount () {
      const number = this.categories.length;

      return number;
    }
  },
  watch: {
    /**
     * @name category
     * @event input category
     */
    category (val) {
      this.$emit("input", Object.assign(this.value, {category: val}));
    }
  }
};
</script>

<template>
  <div class="createContributionStep2">
    <!--
      @name KeywordSelector
    -->
    <KeywordSelector
      v-if="$store.getters.isKeywordServiceEnabled"
      :value="value"
    />
    <h3 class="headline">
      {{ $t("CreateContributionModal.StepCategory.headline") }}
    </h3>
    <form>
      <div v-if="categoryCount < 11">
        <fieldset>
          <legend class="sr-only">
            {{ $t("CreateContributionModal.StepCategory.headline") }}
          </legend>

          <RadioGroup
            v-model="category"
            :options="categoryOptions"
            :icons="categoryIcons"
            :value="category"
          />
        </fieldset>
      </div>

      <div v-else>
        <label
          for="cat"
          class="sr-only"
        >
          {{ $t("CreateContributionModal.StepCategory.headline") }}
        </label>

        <select
          id="cat"
          v-model="category"
        >
          <option
            disabled="disabled"
            value=""
          >
            {{ $t("CreateContributionModal.select") }}
          </option>
          <option
            v-for="categorySelect in categories"
            :key="categorySelect.id"
            :value="categorySelect.id"
          >
            {{ categorySelect.name }}
          </option>
        </select>
      </div>
    </form>
  </div>
</template>

<style>
    div.createContributionStep2 div.radiogroup label.withIcon {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 95%;
    }

    #app.mobile div.createContributionStep2 div.radiogroup {
        padding-bottom: 25%;
    }

    div.createContributionStep2 div.radio-wrapper {
        margin-bottom: 0;
        height: 2.3rem;
    }

    div.createContributionStep2 div.radio-wrapper label:before {
        margin-bottom: 2px;
    }

    div.createContributionStep2 div.radio-wrapper input:focus-visible + label {
        outline: 3px solid #005CA9;
        outline-offset: 5px;
        opacity: 1;
    }
</style>
