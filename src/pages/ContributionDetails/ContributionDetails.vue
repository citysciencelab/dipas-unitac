/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Holds the content for the paragraph planning subarea
 * @displayName ContentPageParagraphPlanningSubarea
 */
import {requestBroker} from "../../mixins/requestBroker.js";
import moment from "moment";
import Masterportal from "../../basicComponents/Masterportal.vue";
import RatingWidget from "../../basicComponents/RatingWidget.vue";
import CommentsForm from "./components/CommentsForm.vue";
import CommentsList from "./components/CommentsList.vue";
import RelatedContributions from "./components/RelatedContributions.vue";

export default {
  name: "ContributionDetails",
  components: {
    Masterportal,
    RatingWidget,
    CommentsForm,
    CommentsList,
    RelatedContributions
  },
  mixins: [requestBroker],
  props: {
    /**
     * holds the contibution id
     */
    id: {
      type: String,
      default: ""
    }
  },
  data () {
    return {
      showForm: true,
      commentsTimestamp: new Date().getTime(),
      contribution: {}
    };
  },
  computed: {
    /**
     * serves the contribution date
     * @returns {String} date of the contribution creation
     */
    created () {
      return moment(this.contribution.created).format("DD.MM.YYYY | HH:mm:ss") + "&nbsp;" + this.$t("ContributionDetails.oClock");
    },
    /**
     * serves the contribution category
     * @returns {String} category name
     */
    category () {
      if (this.contribution.category) {
        return this.$store.getters.categoryName(this.contribution.category.toString());
      }
      return "";
    },
    /**
     * serves the contribution category icon
     * @returns {String} category icon
     */
    categoryIcon () {
      if (this.contribution.category) {
        return this.$store.getters.categoryIcon(this.contribution.category.toString());
      }
      return "";
    },
    /**
     * serves the contribution category rubric
     * @returns {String} category rubric
     */
    rubric () {
      if (this.contribution.rubric) {
        return this.$store.getters.rubricName(this.contribution.rubric.toString());
      }

      return "";
    },
    /**
     * serves the boolean wether geodata is allready there or not
     * @returns {Boolean} has geodata
     */
    hasGeodata () {
      return Object.keys(this.contribution.geodata).length > 0;
    },
    /**
     * serves the masterportal source
     * @returns {String} src
     */
    masterportalSrc () {
      let src = this.$store.getters.singlecontributionmap.url;

      if (this.$root.isMobile) {
        src += (src.indexOf("?") !== -1 ? "&" : "?") + "style=simple";
      }
      return src;
    },
    /**
     * serves the map center
     * @returns {String|Boolean}
     */
    mapCenter () {
      return this.hasGeodata && this.contribution.geodata.geometry.type === "Point"
        ? this.contribution.geodata.geometry.coordinates[0] + "," + this.contribution.geodata.geometry.coordinates[1]
        : false;
    },
    /**
     * serves the map extent
     * @returns {String|Boolean}
     */
    mapExtent () {
      if (this.hasGeodata && this.contribution.geodata.geometry.type !== "Point") {
        const extent = {
            minLon: 999999999,
            minLat: 999999999,
            maxLon: 0,
            maxLat: 0
          },
          latall = [],
          lonall = [];
        let coord = [];

        if (this.contribution.geodata.geometry.type === "LineString") {
          coord = this.contribution.geodata.geometry.coordinates;
        }
        else if (this.contribution.geodata.geometry.type === "Polygon") {
          coord = this.contribution.geodata.geometry.coordinates[0];
        }

        coord.forEach(function (coordinate) {
          latall.push(coordinate[1]);
          lonall.push(coordinate[0]);
        });

        extent.minLon = Math.min(...lonall);
        extent.minLat = Math.min(...latall);
        extent.maxLon = Math.max(...lonall);
        extent.maxLat = Math.max(...latall);

        return extent;
      }
      return false;
    },
    /**
     * serves the boolean wether comments open or not
     * @returns {Boolean}
     */
    commentsOpen () {
      return this.$store.getters.contributionCommentsState === "open";
    },
    /**
     * serves the boolean wether comments shown or not
     * @returns {Boolean}
     */
    displayComments () {
      return this.$store.getters.displayContributionComments;
    },
    /**
     * serves the boolean wether rating is allowed or not
     * @returns {Boolean}
     */
    ratingsAllowed () {
      return this.$store.getters.ratingsAllowed;
    }
  },

  created () {
    this.loadContribution(this.id);
    /**
     * Event will be triggered if the form visibility is set to false (hidden)
     * @event hideCommentForm
     */
    this.$root.$on("hideCommentForm", () => this.setFormVisibility(false));
    /**
     * Event will be triggered if the form visibility is set to true (shown)
     * @event showCommentForm
     */
    this.$root.$on("showCommentForm", () => this.setFormVisibility(true));
    /**
     * Event will be triggered if the form visibility is set to true (shown)
     * @event resetCommentForm
     */
    this.$root.$on("resetCommentForms", () => this.setFormVisibility(true));
  },
  methods: {
    /**
     * Reload the comments and shows the new comment
     * @param {String|Number} newCommentID The ID of the new comment
     * @returns {void}
     */
    reloadComments: function (newCommentID) {
      this.commentsTimestamp = new Date().getTime();
      this.$nextTick(function () {
        this.$scrollTo("#comment-" + newCommentID, 500, {container: "section.content"});
      });
    },
    /**
     * Set the form visibility
     * @param {Boolean} show
     * @returns {void}
     */
    setFormVisibility: function (show) {
      this.showForm = show;
    }
  }
};
</script>

<template>
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-7 col-xl-7 contribution">
        <p class="contributionDetailsLink">
          <router-link to="/contributionlist">
            {{ $t("ContributionDetails.backToList") }}
          </router-link>
        </p>

        <h1>{{ contribution.title }}</h1>

        <p>{{ contribution.text }}</p>

        <!--
          Dipas button component
          @event click acceptCookies
        -->
        <Masterportal
          v-if="contribution.nid && hasGeodata"
          :src="masterportalSrc"
          :contributionID="contribution.nid"
          :center="mapCenter"
          :extent="mapExtent"
        />

        <hr />

        <div class="metaline">
          <div class="meta">
            <p
              class="created"
              v-html="created"
            >
            </p>
            <p class="rubric">
              {{ rubric }}
            </p>
            <p class="category">
              <img
                class="categoryIcon"
                :src="categoryIcon"
                alt=""
              />
              {{ category }}
            </p>
          </div>

          <RatingWidget
            class="rating"
            entityType="node"
            bundle="contribution"
            :entityID="contribution.nid"
            :rating="contribution.rating"
            :ratingsAllowed="ratingsAllowed"
          />
        </div>

        <CommentsForm
          v-if="commentsOpen && showForm"
          :headline="$t('ContributionDetails.comment')"
          formID="nodeCommentForm"
          rootEntityBundle="contribution"
          :rootEntityID="contribution.nid"
          commentedEntityType="node"
          :commentedEntityID="contribution.nid"
          commentSubject=""
          @reloadComments="reloadComments"
        />

        <CommentsList
          v-if="displayComments"
          :key="'comments-' + commentsTimestamp"
          rootEntityBundle="contribution"
          :rootEntityID="contribution.nid"
          :contributionID="contribution.nid"
          :contribution="contribution"
          @reloadComments="reloadComments"
        />

        <p class="contributionDetailsLink">
          <router-link to="/contributionlist">
            {{ $t("ContributionDetails.backToList") }}
          </router-link>
        </p>
      </div>

      <div class="col-lg-1 col-1"></div>

      <RelatedContributions
        class="col-xs-12 col-sm-12 col-lg-4 col-lg-4 col-xl-4"
        :contributionID="contribution.nid"
      />
    </div>
  </div>
</template>

<style>
    #app.mobile div.contribution {
        padding-top: 35px;
    }

    div.contribution h1 {
        color: #003063;
        word-break: break-word;
    }

    div.contribution hr {
        border-color: black;
    }

    div.contribution div.metaline {
        margin-bottom: 30px;
        display: flex;
    }

    div.contribution div.metaline > div {
        display: inline-block;
    }

    div.contribution div.metaline div.meta {
        flex-grow: 1;
    }

    #app.mobile  div.contribution div.metaline div.meta {
        font-size: 0.875rem
    }

    div.contribution div.metaline div.rating {
        width: 225px;
    }

    #app.mobile div.contribution div.metaline div.rating {
        width: 50%;
        padding-left: 4px;
    }

    div.contribution div.metaline p {
        display: block;
        font-size: 0.875rem;
        line-height: 25px;
        margin: 0;
    }

    div.contribution p.contributionDetailsLink a {
      color: #005CA9;
      font-weight: bold;
    }

    div.contribution p.contributionDetailsLink a:focus-visible {
      outline: 3px solid #005CA9;
      outline-offset: 2px;
    }

    div.contribution div.metaline p img.categoryIcon {
        max-height: 20px;
    }
</style>
