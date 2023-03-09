/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The contribution list teaser
 * @displayName ContributionListTeaser
 */
import moment from "moment";
import RatingWidget from "../../../basicComponents/RatingWidget.vue";

export default {
  name: "ContributionListTeaser",
  components: {
    RatingWidget
  },
  props: {
    /**
     * holds the teaser data object
     */
    teaser: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      rndid: "id" + Math.floor(Math.random() * 100000000)
    };
  },
  computed: {
    /**
     * serves the link to details
     * @returns {String}
     */
    detaillink () {
      return "/contribution/" + this.teaser.nid;
    },
    /**
     * serves the category icon
     * @returns {String}
     */
    categoryIcon () {
      return this.$store.getters.categoryIcon(this.teaser.category);
    },
    /**
     * serves the category name
     * @returns {String}
     */
    categoryName () {
      return this.$store.getters.categoryName(this.teaser.category);
    },
    /**
     * serves the rubric name
     * @returns {String}
     */
    rubricName () {
      return this.$store.getters.rubricName(this.teaser.rubric);
    },
    /**
     * serves initally the date and time when the teaser was created
     * @returns {String} date and time when the teaser was created
     */
    created () {
      return moment(this.teaser.created).format(this.$t("ContributionList.ContributionListTeaser.datetimeFormat")) + " " + this.$t("ContributionList.ContributionListTeaser.oClock");
    }
  }
};
</script>

<template>
  <article
    class="contributionteaser"
  >
    <div
      class="inner"
      tabindex="0"
      role="link"
      @click="$router.push(detaillink)"
      @keyup.enter="$router.push(detaillink)"
    >
      <img
        class="categoryIcon"
        :src="categoryIcon"
        :alt="categoryName"
      />

      <p class="rubric">
        {{ rubricName }}
      </p>
      <div class="textContent">
        <h2
          :id="rndid"
        >
          {{ teaser.title }}
        </h2>
        <p
          :aria-describedby="rndid"
          class="detailLink"
          role="link"
        >
          {{ $t("ContributionList.ContributionListTeaser.routeToEntry") }}<i class="material-icons">play_arrow</i>
        </p>
        <hr />
        <div class="subline">
          <div class="meta">
            <p class="created">
              {{ created }}
            </p>
            <p class="category">
              {{ categoryName }}
            </p>
          </div>

          <div class="activity">
            <!--
              @name RatingWidget
            -->
            <RatingWidget
              class="rating"
              entityType="node"
              widgetStyle="simple"
              :entityID="teaser.nid"
              :rating="{upVotes: teaser.upVotes, downVotes: teaser.downVotes}"
            />
            <p class="comments">
              {{ teaser.comments === 1 ? $t("ContributionList.ContributionListTeaser.comment", {"teaserComments": teaser.comments}) : $t("ContributionList.ContributionListTeaser.comments", {"teaserComments": teaser.comments}) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </article>
</template>

<style>
    article.contributionteaser {
        padding: 10px;
        position: relative;
        cursor: pointer;
    }

    article.contributionteaser div.inner {
        background-color: #F0F0F0;
        padding: 10px 32px 32px 32px;
    }

    article.contributionteaser div.inner img.categoryIcon {
        height: 40px;
        position: relative;
        top: -20px;
        margin: 0 0 -10px -10px
    }

    article.contributionteaser div.inner p.rubric {
        position: absolute;
        top: 20px;
        right: 32px;
        font-size: 0.8rem;
        margin-left: 10px;
        margin-right: 10px;
    }

    article.contributionteaser div.inner div.textContent h2 {
      margin-top: -5px;
    }

    article.contributionteaser div.inner div.textContent h2 {
        height: 50px;
        font-size: 1rem;
        font-weight: bold;
    }

    article.contributionteaser div.inner div.textContent p.detailLink,
    article.contributionteaser div.inner div.textContent p.detailLink i {
        vertical-align: middle;
        text-align: right;
        line-height: 1rem;
        font-size: 1rem;
        font-weight: bold;
        margin: 0;
        padding: 0;
        white-space: nowrap;
    }

    article.contributionteaser div.inner div.textContent p.detailLink {
        cursor: pointer;
        color: #005CA9;
    }

    article.contributionteaser div.inner div.textContent hr {
        border-color: black;
        margin: 6px 0;
    }

    article.contributionteaser div.inner div.textContent div.subline {
        display: flex;
        vertical-align: top;
        margin-top: 10px;
    }

    article.contributionteaser div.inner div.textContent div.subline > div {
        display: inline-block;
        margin: 0;
    }

    article.contributionteaser div.inner div.textContent div.subline > div.meta {
        flex-grow: 1;
    }

    article.contributionteaser div.inner div.textContent div.subline > div.activity {
        width: 80px;
        text-align: justify;
        margin-top: -10px;
        margin-bottom: -8px;
    }

    article.contributionteaser div.inner div.textContent div.subline > div.activity p.comments {
      display: inline-block;
    }

    article.contributionteaser div.inner div.textContent div.subline div p {
        font-size: 0.8rem;
        line-height: 1rem;
        margin: 0 0 5px 0;
        padding: 0;
        white-space: nowrap;
    }

    article.contributionteaser div.inner div.textContent div.subline div p.category {
        white-space: nowrap;
        max-width: 320px;
        overflow: hidden;
        width: 100%;
        text-overflow: ellipsis;
    }

    article.contributionteaser div.inner div.textContent div.subline div.meta {
        overflow: hidden;
        padding-right: 10px;
    }

    article.contributionteaser div.inner div.textContent div.subline div.meta p.category {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
