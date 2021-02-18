/**
 * @license GPL-2.0-or-later
 */

<script>
import moment from "moment";
import RatingWidget from "../../../basicComponents/RatingWidget.vue";

export default {
  name: "ContributionListTeaser",
  components: {
    RatingWidget
  },
  props: {
    teaser: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  computed: {
    detaillink () {
      return "/contribution/" + this.teaser.nid;
    },
    categoryIcon () {
      return this.$store.getters.categoryIcon(this.teaser.category);
    },
    categoryName () {
      return this.$store.getters.categoryName(this.teaser.category);
    },
    rubricName () {
      return this.$store.getters.rubricName(this.teaser.rubric);
    },
    created () {
      return moment(this.teaser.created).format("DD.MM.YYYY | HH:mm") + " Uhr";
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
      @click="$router.push(detaillink)"
    >
      <img
        class="categoryIcon"
        :src="categoryIcon"
      />

      <p class="rubric">
        {{ rubricName }}
      </p>
      <div class="textContent">
        <h2>{{ teaser.title }}</h2>
        <p class="detailLink">
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
        right: 20px;
        font-size: 10px;
        margin-left: 10px;
    }

    article.contributionteaser div.inner div.textContent h2 {
        height: 50px;
        font-size: 16px;
        font-weight: bold;
    }

    article.contributionteaser div.inner div.textContent p.detailLink,
    article.contributionteaser div.inner div.textContent p.detailLink i {
        vertical-align: middle;
        text-align: right;
        line-height: 12px;
        font-size: 12px;
        margin: 0;
        padding: 0;
        white-space: nowrap;
    }

    article.contributionteaser div.inner div.textContent p.detailLink {
        cursor: pointer;
        color: #2573B4;
    }

    article.contributionteaser div.inner div.textContent p.detailLink:hover {
        text-decoration: underline;
    }

    article.contributionteaser div.inner div.textContent hr {
        border-color: black;
        margin: 6px 0;
    }

    article.contributionteaser div.inner div.textContent div.subline {
        display: flex;
        vertical-align: top;
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

    article.contributionteaser div.inner div.textContent div.subline div p {
        font-size: 10px;
        line-height: 10px;
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
