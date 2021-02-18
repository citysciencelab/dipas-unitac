/**
 * @license GPL-2.0-or-later
 */

<script>
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
      headline: ""
    };
  },
  computed: {
    ...mapGetters([
      "allCategories",
      "allRubrics",
      "useRubrics"
    ]),
    topContributionsByCommentCount () {
      return _.sortBy(_.clone(this.statistics.contributionData), (contribution) => Number(contribution.comments)).reverse().slice(0, 10);
    },
    topContributionsByRating: function () {
      return _.sortBy(_.clone(this.statistics.contributionData), (contribution) => Number(contribution.rating)).reverse().slice(0, 10);
    },
    contributionCount () {
      return this.statistics.contributionData.length;
    },
    contributionCategories () {
      return _.uniq(_.pluck(this.statistics.contributionData, "category"), false);
    },
    contributionCategoryLabels () {
      const labels = [];

      _.each(this.contributionCategories, function (category) {
        labels.push(this.$store.getters.categoryName(category));
      }, this);
      return labels;
    },
    contributionCategoryColors () {
      const colors = [];

      _.each(this.contributionCategories, function (category) {
        colors.push(this.$store.getters.categoryColor(category));
      }, this);
      return colors;
    },
    contributionsByCategory () {
      const counts = [];

      _.each(this.contributionCategories, function (category) {
        counts.push(_.filter(this.statistics.contributionData, (contribution) => contribution.category === category).length);
      }, this);
      return counts;
    },
    contributionRubrics () {
      return _.uniq(_.pluck(this.statistics.contributionData, "rubric"), false);
    },
    contributionRubricLabels () {
      const labels = [];

      _.each(this.contributionRubrics, function (rubric) {
        labels.push(this.$store.getters.rubricName(rubric));
      }, this);
      return labels;
    },
    contributionRubricColors () {
      const rubricColors = ["#40648B", "#537396", "#6683A2", "#7992AE", "#8CA2B9", "#9FB1C5", "#B3C1D1", "#C5D0DC", "#D9E0E8", "#ECEFF3"],
        noRubrics = this.allRubrics.length > 0 ? this.allRubrics.length : rubricColors.length,
        colorStep = parseInt(rubricColors.length / noRubrics, 10),
        useColors = [];

      let colorIndex = 0;

      for (let i = 0; i < noRubrics; i++) {
        useColors.push(rubricColors[colorIndex]);
        colorIndex += colorStep;
      }

      return useColors;
    },
    contributionsByRubric () {
      const counts = [];

      _.each(this.contributionRubrics, function (rubric) {
        counts.push(_.filter(this.statistics.contributionData, (contribution) => contribution.rubric === rubric).length);
      }, this);
      return counts;
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
            <StatisticsNumber
              :headline="$t('Statistics.StatisticsNumber.headlineCountEntries')"
              :value="contributionCount"
            />
          </div>

          <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 statisticsContainer statisticsNumber statisticsNumberComments">
            <StatisticsNumber
              :headline="$t('Statistics.StatisticsNumber.headlineCountComments')"
              :value="statistics.comments"
            />
          </div>

          <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 statisticsContainer statisticsDonut statisticsDonutCategories">
            <StatisticsDonut
              :key="'categorydonut' + asyncRenderKey"
              :headline="$t('Statistics.StatisticsDonut.headlineCat')"
              :keyId="'categorydonut' + asyncRenderKey"
              :labels="contributionCategoryLabels"
              :colors="contributionCategoryColors"
              :donutData="contributionsByCategory"
            />
          </div>

          <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-xs-12 statisticsContainer statisticsDonut statisticsDonutRubrics">
            <StatisticsDonut
              v-if="useRubrics"
              :key="'rubricdonut' + asyncRenderKey"
              :headline="$t('Statistics.StatisticsDonut.headlineType')"
              :keyId="'rubricdonut' + asyncRenderKey"
              :labels="contributionRubricLabels"
              :donutData="contributionsByRubric"
              :colors="contributionRubricColors"
            />
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 statisticsContainer nodeTopList nodeTopListComments">
            <StatisticsTopNodeList
              :headline="$t('Statistics.StatisticsTopNodeListComments.headline')"
              :nodeList="topContributionsByCommentCount"
              routerlink="/contribution/"
            />
          </div>

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-xs-12 statisticsContainer nodeTopList nodeTopListRating">
            <StatisticsTopNodeList
              :headline="$t('Statistics.StatisticsTopNodeListRatings.headline')"
              :nodeList="topContributionsByRating"
              routerlink="/contribution/"
            />
          </div>
        </div>
      </div>

      <div class="col-lg-1 col-xl-1"></div>

      <RightColumn class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4" />
    </div>
  </div>
</template>

<style>
    #app.mobile div.statistics h1 {
        padding-top: 35px;
    }

    div.statistics h1 {
        font-size: 36px;
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

    div.statistics div.statisticsContainer > div > p.headline {
        font-size: 20px;
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
        font-size: 14px;
        font-weight: bold;
        color: black;
    }

    div.statistics div.nodeTopList ol li:before {
        display: inline-block;
        width: 17px;
        text-align: right;
        margin-right: 5px;
        content: counter(list-counter);
        font-size: 14px;
        color: #40648B;
        font-weight: bold;
    }

    div.statistics div.statisticsNumber p.number {
        font-weight: 700;
        font-size: 70px;
        color: #40648B;
        word-break: keep-all;
        text-align: center;
        margin: 30px 0;
    }
</style>
