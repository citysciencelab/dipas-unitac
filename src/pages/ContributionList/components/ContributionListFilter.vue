/**
 * @license GPL-2.0-or-later
 */

<script>
import {mapGetters} from "vuex";
import JSum from "jsum";
import ContributionListFilterTermSelect from "./ContributionListFilterTermSelect.vue";
import DipasButton from "../../../basicComponents/DipasButton.vue";
import RadioGroup from "../../../basicComponents/RadioGroup.vue";

export default {
  name: "ContributionListFilter",
  components: {
    ContributionListFilterTermSelect,
    RadioGroup,
    DipasButton
  },
  props: {
    filtersApplied: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      text: "",
      headline: "",
      termTitle: "",
      termTitlePlural: "",
      filters: {
        category: this.filtersApplied.filters.category,
        rubric: this.filtersApplied.filters.rubric
      },
      sort: {
        field: this.filtersApplied.sort.field,
        direction: this.filtersApplied.sort.direction
      },
      sortOptions: {
        field: {
          0: {
            val: "created",
            label: this.$t("ContributionList.ContributionListFilter.sortOptions.date")
          },
          1: {
            val: "rating",
            label: this.$t("ContributionList.ContributionListFilter.sortOptions.rating")
          },
          2: {
            val: "comments",
            label: this.$t("ContributionList.ContributionListFilter.sortOptions.comments")
          }
        },
        direction: {
          0: {
            val: "DESC",
            label: this.$t("ContributionList.ContributionListFilter.sortOptions.desc")
          },
          1: {
            val: "ASC",
            label: this.$t("ContributionList.ContributionListFilter.sortOptions.asc")
          }
        }
      }
    };
  },
  computed: {
    ...mapGetters([
      "allCategories",
      "allRubrics",
      "useRubrics"
    ]),
    filtersConfigured () {
      return {
        filters: this.pickFilters(this.filters),
        sort: this.sort
      };
    },
    filterConfiguredHash () {
      return JSum.digest(this.filtersConfigured, "SHA256", "hex");
    },
    filterAppliedHash () {
      const compareBase = {
        filters: this.pickFilters(this.filtersApplied.filters),
        sort: this.filtersApplied.sort
      };

      return JSum.digest(compareBase, "SHA256", "hex");
    },
    filterChanged () {
      return this.filterConfiguredHash !== this.filterAppliedHash;
    },
    categoryOptions () {
      const options = {};

      Object.entries(this.allCategories).forEach(([index, catData]) => {
        options[index] = {
          val: catData.id,
          label: catData.name,
          field_category_icon: catData.field_category_icon
        };
      });

      return options;
    },
    rubricOptions () {
      const options = {};

      Object.entries(this.allRubrics).forEach(([index, rubData]) => {
        options[index] = {
          val: rubData.id,
          label: rubData.name
        };
      });

      return options;
    }
  },
  methods: {
    applyFilters: function () {
      if (this.filters.category.length === Object.keys(this.allCategories).length) {
        this.filters.category = [];
      }
      if (this.filters.rubric.length === Object.keys(this.allRubrics).length) {
        this.filters.rubric = [];
      }
      this.$emit("filter", {filters: this.filters, sort: this.sort});
    },
    resetFilters: function () {
      this.filters = {
        category: [],
        rubric: []
      };
      this.sort = {
        field: "created",
        direction: "DESC"
      };
      this.$emit("resetFilters");
    },
    pickFilters: function (filters) {
      const result = {};

      Object.entries(filters).forEach(([index, filter]) => {

        if (filter.length) {
          result[index] = filter;
        }

      });

      return result;
    }
  }
};
</script>

<template>
  <section class="filter">
    <p class="headline">
      <i class="material-icons">filter_list</i>
      {{ $t("ContributionList.ContributionListFilter.filterOptions") }}
    </p>

    <hr />

    <ContributionListFilterTermSelect
      v-model="filters.category"
      :headline="$t('ContributionList.ContributionListFilter.termSelectHeadlineCat')"
      :termTitle="$t('ContributionList.ContributionListFilter.termSelectTitleCat')"
      :termTitlePlural="$t('ContributionList.ContributionListFilter.termSelectTitleCatPlural')"
      :terms="categoryOptions"
      icon="field_category_icon"
      class="termSelect"
    />

    <ContributionListFilterTermSelect
      v-if="useRubrics"
      v-model="filters.rubric"
      :headline="$t('ContributionList.ContributionListFilter.termSelectHeadlineType')"
      :termTitle="$t('ContributionList.ContributionListFilter.termSelectTitleType')"
      :termTitlePlural="$t('ContributionList.ContributionListFilter.termSelectTitleTypePlural')"
      :terms="rubricOptions"
      class="termSelect"
    />

    <div class="orderSection">
      <p class="headline">
        {{ $t('ContributionList.ContributionListFilter.sortBy') }}
      </p>

      <RadioGroup
        v-model="sort.field"
        class="orderFieldRadioGroup"
        :options="sortOptions.field"
        :value="sort.field"
      />

      <RadioGroup
        v-model="sort.direction"
        class="orderDirectionRadioGroup"
        :options="sortOptions.direction"
        :value="sort.direction"
      />
    </div>

    <p v-if="filterChanged">
      {{ $t("ContributionList.ContributionListFilter.filterChanges") }}
    </p>

    <DipasButton
      :text="$t('ContributionList.ContributionListFilter.useFilter')"
      class="blue angular"
      @click="applyFilters"
    />

    <DipasButton
      :text="$t('ContributionList.ContributionListFilter.resetFilter')"
      class="grey angular"
      @click="resetFilters"
    />
  </section>
</template>

<style>
    section.filter button {
        width: 100%;
    }
    section.filter button.blue {
        margin-bottom: 20px;
    }
    section.filter p.headline {
        font-size: 24px;
        font-weight: bold;
        color: #003063;
    }

    section.filter p.headline .material-icons {
        position: relative;
        top: 8px;
        font-size: 40px;
        line-height: 20px;
        margin-right: 20px;
    }

    section.filter hr {
        border-color: black;
    }

    section.filter .termSelect {
        margin-bottom: 20px;
    }

    section.filter div.orderSection {
        margin-bottom: 20px;
    }

    section.filter div.orderSection .radiogroup {
        display: inline-block;
        width: 50%;
        vertical-align: top;
    }

    section.filter div.orderSection p.headline {
        font-weight: bold;
        font-size: 16px;
    }

    #app.mobile div.customModal.filterModal.modalMobile div.modalContent {
        height: calc(var(--vh, 1vh) * 100);
        overflow-y: auto;
        padding-bottom: 100px;
    }
</style>
