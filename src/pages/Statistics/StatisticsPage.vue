/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the frame of the statistic page
 * @displayName StatisticsPage
 */
import _ from "underscore";
import {requestBroker} from "../../mixins/requestBroker.js";
import {mapGetters} from "vuex";
import StatisticsTopNodeList from "./components/StatisticsTopNodeList.vue";
import StatisticsNumber from "./components/StatisticsNumber.vue";
import StatisticsDonut from "./components/StatisticsDonut.vue";
import RightColumn from "../../basicComponents/RightColumn.vue";

export default {
  name: "StatisticsPage",
  components: {
    StatisticsTopNodeList,
    StatisticsNumber,
    StatisticsDonut,
    RightColumn
  },
  mixins: [requestBroker],
  data () {
    return {
      asyncRenderKey: window.performance.now(),
      statistics: {
        comments: 0,
        contributionData: []
      },
      headline: "",
      rubricTableActive: false,
      categoryTableActive: false
    };
  },
  computed: {
    ...mapGetters([
      "allCategories",
      "allRubrics",
      "useRubrics"
    ]),
    /**
     * @name topContributionsByCommentCount
     * @returns {Array}
     */
    topContributionsByCommentCount () {
      return _.sortBy(_.clone(this.statistics.contributionData), (contribution) => Number(contribution.comments)).reverse().slice(0, 10);
    },
    /**
     * @name topContributionsByRating
     * @returns {Array}
     */
    topContributionsByRating: function () {
      return _.sortBy(_.clone(this.statistics.contributionData), (contribution) => Number(contribution.rating)).reverse().slice(0, 10);
    },
    /**
     * @name contributionCount
     * @returns {Number}  the value of the counted contributions
     */
    contributionCount () {
      return this.statistics.contributionData.length;
    },
    /**
     * @name contributionCategories
     * @returns {Array}
     */
    contributionCategories () {
      return _.uniq(_.pluck(this.statistics.contributionData, "category"), false);
    },
    /**
     * @name contributionCategoryLabels
     * @returns {Array} labels
     */
    contributionCategoryLabels () {
      const labels = [];

      _.each(this.contributionCategories, function (category) {
        labels.push(this.$store.getters.categoryName(category));
      }, this);
      return labels;
    },
    /**
     * @name contributionCategoryColors
     * @returns {Array} colors
     */
    contributionCategoryColors () {
      const colors = [];

      _.each(this.contributionCategories, function (category) {
        colors.push(this.$store.getters.categoryColor(category));
      }, this);
      return colors;
    },
    /**
     * @name contributionsByCategory
     * @returns {Array} contribution counts by category
     */
    contributionsByCategory () {
      const counts = [];

      _.each(this.contributionCategories, function (category) {
        counts.push(_.filter(this.statistics.contributionData, (contribution) => contribution.category === category).length);
      }, this);
      return counts;
    },
    contributionsByCategoryAsText () {
      const
        rndidCats = "cat" + Math.floor(Math.random() * 1000000),
        rndidContribs = "contrib" + Math.floor(Math.random() * 1000000),
        catCount = this.contributionCategories.length;
      let catTextAlt =
        "<table class='scr-only'><thead><tr><th id='" +
        rndidCats +
        "'>" +
        this.$t("Statistics.StatisticsNumber.headlineCategory") +
        "</th><th id='" +
        rndidContribs +
        "'>" +
        this.$t("Statistics.StatisticsNumber.headlineCountEntries") +
        "</th></tr></thead><tbody>";

      for (let i = 0; i < catCount; i++) {
        catTextAlt += "<tr><td headers='" +
        rndidCats +
        "'>" +
        this.contributionCategoryLabels[i] +
        "</td><td headers='" +
        rndidContribs +
        "'>" +
        this.contributionsByCategory[i] +
        "</td></tr>";
      }
      catTextAlt += "</tbody></table>";

      return catTextAlt;
    },
    /**
     * @name contributionRubrics
     * @returns {Array} rubrics
     */
    contributionRubrics () {
      return _.uniq(_.pluck(this.statistics.contributionData, "rubric"), false);
    },
    /**
     * @name contributionRubricLabels
     * @returns {Array} labels
     */
    contributionRubricLabels () {
      const labels = [];

      _.each(this.contributionRubrics, function (rubric) {
        labels.push(this.$store.getters.rubricName(rubric));
      }, this);
      return labels;
    },
    /**
     * @name contributionRubricColors
     * @returns {Array} used colors
     */
    contributionRubricColors () {
      const rubricColors = ["#003063", "#703980", "#C63C76", "#FB624F", "#FFA600", "#8F9800", "#107C10", "#488F31", "#BAD0AF", "#033005", "#FFFFFF", "#000000"],
        noRubrics = this.allRubrics.length > 0 ? this.allRubrics.length : rubricColors.length,
        colorStep = parseInt(rubricColors.length / noRubrics, 12),
        useColors = [];

      let colorIndex = 0;

      for (let i = 0; i < noRubrics; i++) {
        useColors.push(rubricColors[colorIndex]);
        colorIndex += colorStep;
      }

      return useColors;
    },
    /**
     * @name contributionsByRubric
     * @returns {Array}
     */
    contributionsByRubric () {
      const counts = [];

      _.each(this.contributionRubrics, function (rubric) {
        counts.push(_.filter(this.statistics.contributionData, (contribution) => contribution.rubric === rubric).length);
      }, this);
      return counts;
    },
    contributionsByRubricAsText () {
      const
        rndidRubrics = "rubric" + Math.floor(Math.random() * 1000000),
        rndidContribs = "contrib" + Math.floor(Math.random() * 1000000),
        rubricCount = this.contributionRubrics.length;
      let rubricTextAlt =
        "<table class='scr-only'><thead><tr><th id='" +
        rndidRubrics +
        "'>" +
        this.$t("Statistics.StatisticsNumber.headlineRubric") +
        "</th><th id='" +
        rndidContribs +
        "'>" +
        this.$t("Statistics.StatisticsNumber.headlineCountEntries") +
        "</th></tr></thead><tbody>";

      for (let i = 0; i < rubricCount; i++) {
        rubricTextAlt += "<tr><td headers='" +
        rndidRubrics +
        "'>" +
        this.contributionRubricLabels[i] +
        "</td><td headers='" +
        rndidContribs +
        "'>" +
        this.contributionsByRubric[i] +
        "</td></tr>";
      }
      rubricTextAlt += "</tbody></table>";

      return rubricTextAlt;
    }
  },
  watch: {
    // This watcher is used to determine if the structural data was loaded
    contributionCategoryLabels: {
      deep: true,
      handler () {
        this.asyncRenderKey = window.performance.now();
      }
    }
  },
  beforeMount () {
    /**
     * loads initally the statistic data object from requestbroker drupal api
     * @returns {void}
     */
    this.loadStatisticalData();
  }
};
</script>

<template>
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-7 col-xl-7 statistics">
        <h1>{{ $t("Statistics.headline") }}</h1>

        <div class="row">
          <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 statisticsContainer statisticsNumber statisticsNumberContributions">
            <!--
              @name StatisticsNumber
            -->
            <StatisticsNumber
              :headline="$t('Statistics.StatisticsNumber.headlineCountEntries')"
              :value="contributionCount"
            />
          </div>

          <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 statisticsContainer statisticsNumber statisticsNumberComments">
            <!--
              @name StatisticsNumber
            -->
            <StatisticsNumber
              :headline="$t('Statistics.StatisticsNumber.headlineCountComments')"
              :value="statistics.comments"
            />
          </div>

          <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 statisticsContainer statisticsDonut statisticsDonutCategories">
            <!--
              @name StatisticsDonat
            -->
            <StatisticsDonut
              :key="'categorydonut' + asyncRenderKey"
              :headline="$t('Statistics.StatisticsDonut.headlineCat')"
              :keyId="'categorydonut' + asyncRenderKey"
              :labels="contributionCategoryLabels"
              :colors="contributionCategoryColors"
              :donutData="contributionsByCategory"
              :textAlternative="contributionsByCategoryAsText"
            />
            <p
              class="displayAsLink"
              tabindex="0"
              @click="categoryTableActive = !categoryTableActive"
              @keyup.enter="categoryTableActive = !categoryTableActive"
            >
              {{ categoryTableActive ? $t("Statistics.StatisticsAccessibleTables.hideTable") : $t("Statistics.StatisticsAccessibleTables.showTable") }}
            </p>
            <div
              v-if="categoryTableActive"
              v-html="contributionsByCategoryAsText"
            >
            </div>
          </div>
          <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 statisticsContainer statisticsDonut statisticsDonutRubrics">
            <!--
              @name StatisticsDonat
            -->
            <StatisticsDonut
              v-if="useRubrics"
              :key="'rubricdonut' + asyncRenderKey"
              :headline="$t('Statistics.StatisticsDonut.headlineType')"
              :keyId="'rubricdonut' + asyncRenderKey"
              :labels="contributionRubricLabels"
              :donutData="contributionsByRubric"
              :colors="contributionRubricColors"
              :textAlternative="contributionsByRubricAsText"
            />
            <p
              class="displayAsLink"
              tabindex="0"
              @click="rubricTableActive = !rubricTableActive"
              @keyup.enter="rubricTableActive = !rubricTableActive"
            >
              {{ rubricTableActive ? $t("Statistics.StatisticsAccessibleTables.hideTable") : $t("Statistics.StatisticsAccessibleTables.showTable") }}
            </p>
            <div
              v-if="rubricTableActive"
              v-html="contributionsByRubricAsText"
            >
            </div>
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 statisticsContainer nodeTopList nodeTopListComments">
            <!--
              @name StatisticsTopNodeList
            -->
            <StatisticsTopNodeList
              :headline="$t('Statistics.StatisticsTopNodeListComments.headline')"
              :nodeList="topContributionsByCommentCount"
              routerlink="/contribution/"
            />
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 statisticsContainer nodeTopList nodeTopListRating">
            <!--
              @name StatisticsTopNodeList
            -->
            <StatisticsTopNodeList
              :headline="$t('Statistics.StatisticsTopNodeListRatings.headline')"
              :nodeList="topContributionsByRating"
              routerlink="/contribution/"
            />
          </div>
        </div>
      </div>

      <div class="col-lg-1 col-xl-1"></div>
      <!--
        @name RightColumn
      -->
      <RightColumn class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4" />
    </div>
  </div>
</template>

<style>
    #app.mobile div.statistics h1 {
        padding-top: 35px;
    }

    div.statistics h1 {
        font-size: 2.25rem;
        font-weight: bold;
        color: #003063;
        word-break: break-word;
        margin-bottom: 40px;
    }

    div.statistics .col-6 {
        padding-left: 7px;
        padding-right: 7px;
    }

    div.statistics div.statisticsContainer {
        margin-bottom: 20px;
    }

    div.statistics div.statisticsContainer > div {
        width: 100%;
        background-color: #F0F0F0;
        padding: 20px;
    }

    div.statistics div.statisticsContainer > div > h3.headline,
    div.statistics div.statisticsContainer > div > div > h3.headline {
        font-size: 1.5rem;
        font-weight: bold;
        color: #003063;
        word-break: break-word;
        margin: 0 0 10px 0;
    }

    div.statistics div.nodeTopList ol {
        list-style-type: none;
        list-style-position: outside;
        list-style-image: none;
        margin: 0;
        padding: 0;
        counter-reset: list-counter;
    }

    div.statistics div.nodeTopList ol li {
        counter-increment: list-counter;
        white-space: nowrap;
        overflow: hidden;
        width: 100%;
        text-overflow: ellipsis;
    }

    div.statistics div.nodeTopList ol li a {
        font-size: 1rem;
        font-weight: bold;
        color: #005CA9;
    }

    div.statistics div.nodeTopList ol li:before {
        display: inline-block;
        width: 17px;
        text-align: right;
        margin-right: 5px;
        content: counter(list-counter);
        font-size: 1rem;
        color: #40648B;
        font-weight: bold;
    }

    div.statistics div.statisticsNumber p.number {
        font-weight: 700;
        font-size: 4.375rem;
        color: #40648B;
        word-break: keep-all;
        text-align: center;
        margin: 30px 0;
    }

    div.statisticsContainer table.scr-only {
      border: 1px solid #a0a0a0;
      width: 100%;
      background-color: #FFFFFF
    }

    div.statisticsContainer table.scr-only  th {
      font-weight: bold;
    }

    div.statisticsContainer table.scr-only  td, th {
      border: 1px solid #a0a0a0;
      padding: 4px 30px;
      text-align: center;
    }

    div.statisticsContainer p.displayAsLink {
      font-size: 1rem;
      font-weight: bold;
      color: #005CA9;
      text-align: center;
      margin-top: 5px;
      cursor: pointer;
    }
</style>
