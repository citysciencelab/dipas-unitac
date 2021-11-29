/**
 * @license GPL-2.0-or-later
 */

<script>
import _ from "underscore";
import {ContentPageDynamicContentElement} from "./components/ContentPageDynamicContentElement.js";
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPageParagraphText from "./components/ContentPageParagraphText.vue";
import ContentPageParagraphImage from "./components/ContentPageParagraphImage.vue";
import ContentPageParagraphVideo from "./components/ContentPageParagraphVideo.vue";
import ContentPageParagraphAccordeon from "./components/ContentPageParagraphAccordeon.vue";
import ContentPageParagraphDivisionInPlanningSubareas from "./components/ContentPageParagraphDivisionInPlanningSubareas.vue";
import ContentPageParagraphConception from "./components/ContentPageParagraphConception.vue";
import ContentPageNodeConception from "./components/ContentPageNodeConception.vue";
import ProjectPartnerBlock from "../../basicComponents/ProjectPartnerBlock.vue";
import RightColumn from "../../basicComponents/RightColumn.vue";
import RatingWidget from "../../basicComponents/RatingWidget.vue";
import CommentsForm from "../ContributionDetails/components/CommentsForm.vue";
import CommentsList from "../ContributionDetails/components/CommentsList.vue";
import DipasButton from "../../basicComponents/DipasButton.vue";
/**
 * Shows the content page
 * @displayName ContentPage
 */
export default {
  name: "ContentPage",
  components: {
    ContentPageParagraphText,
    ContentPageParagraphImage,
    ContentPageParagraphVideo,
    ContentPageParagraphAccordeon,
    ContentPageParagraphDivisionInPlanningSubareas,
    ContentPageParagraphConception,
    ContentPageNodeConception,
    RightColumn,
    ProjectPartnerBlock,
    RatingWidget,
    CommentsForm,
    CommentsList,
    DipasButton
  },
  mixins: [ContentPageDynamicContentElement, requestBroker],
  props: {
    content: {
      /**
       * The content data object
       */
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      pageContent: {
        title: "",
        content: []
      },
      showPartnerLogo: false,
      showRatingWidget: false,
      commentsOpen: false,
      showForm: false,
      showCommentList: false,
      commentsTimestamp: new Date().getTime(),
      RightColumn: "RightColumn",
      ratingsAllowed: false,
      commentsFormHeadline: "",
      cookieButtonClicked: false
    };
  },
  computed: {
    /**
     * holds the dynamic variable
     * @returns {Boolean} wether to show the "Accept Cookies"- button
     */
    showCookiesButton () {
      return !_.isUndefined(this.showAcceptCookiesButton) && this.showAcceptCookiesButton;
    }
  },
  watch: {
    /**
     * if static content changes serve it in variable pageContent
     * @returns {void}
     */
    staticContent: {
      deep: true,
      handler: function () {
        this.pageContent = this.staticContent;
      }
    },
    /**
     * if content changes serve it in variable pageContent
     * @returns {void}
     */
    content () {
      if (Object.keys(this.content).length) {
        this.pageContent = this.content;
      }
    }
  },
  /**
   * Check on mounted lifecycle the data object and save it in variables
   * @returns {void}
   */
  mounted () {
    if (!_.isUndefined(this.content) && Object.keys(this.content).length) {
      this.pageContent = this.content;
    }
    if (!_.isUndefined(this.staticContent) && Object.keys(this.staticContent).length) {
      this.pageContent = this.staticContent;
    }
  },
  /**
   * Resets the comment form and shows it
   * @returns {void}
   */
  created () {
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
     * Set the cookies confirmed
     * @returns {void}
     */
    acceptCookies: function () {
      this.$root.cookieBannerConfirmed = true;
      this.cookieButtonClicked = true;
      this.confirmCookies();
    },
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
    <div class="row contentPage">
      <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7 col-xl-7">
        <h1>{{ pageContent.title }}</h1>

        <!--
          Dynamic component
          @property {Object} pageContent
        -->
        <component
          :is="getComponent(element)"
          v-for="element in pageContent.content"
          :key="getComponent(element).name"
          :content="element"
        />

        <!--
          Dipas button component
          @event click acceptCookies
        -->
        <DipasButton
          v-if="showCookiesButton"
          :text="$t('Cookiebar.buttontext')"
          class="blue angular"
          @click="acceptCookies"
        />

        <!--
          ProjectPartnerBlock component
        -->
        <ProjectPartnerBlock v-if="showPartnerLogo" />

        <hr v-if="showRatingWidget || commentsOpen || showCommentList" />

        <div
          v-if="showRatingWidget"
          class="metaline"
        >
          <!--
            RatingWidget component
            @property {Object} pageContent
          -->
          <RatingWidget
            class="rating"
            entityType="node"
            :bundle="pageContent.bundle"
            :entityID="pageContent.nid"
            :rating="pageContent.rating"
            :ratingsAllowed="ratingsAllowed"
          />
        </div>

        <!--
          CommentsForm component
          @fires reloadComments
          @property {Object} pageContent
        -->
        <CommentsForm
          v-if="commentsOpen && showForm"
          :headline="commentsFormHeadline"
          formID="nodeCommentForm"
          :rootEntityBundle="pageContent.bundle"
          :rootEntityID="pageContent.nid"
          commentedEntityType="node"
          :commentedEntityID="pageContent.nid"
          commentSubject=""
          @reloadComments="reloadComments"
        />

        <!--
          CommentsList component
          @fires reloadComments
          @property {Object} pageContent
        -->
        <CommentsList
          v-if="showCommentList"
          :key="'comments-' + commentsTimestamp"
          :rootEntityBundle="pageContent.bundle"
          :rootEntityID="pageContent.nid"
          :contributionID="pageContent.nid"
          :contribution="pageContent"
          @reloadComments="reloadComments"
        />
      </div>

      <div class="col-md-1 col-lg-1 col-xl-1"></div>

      <!--
        Dynamic component for the right column
        @property {Object} RightColumn
      -->
      <component
        :is="RightColumn"
        :key="RightColumn.name"
        class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4"
      />
    </div>
  </div>
</template>

<style>
    #app.mobile div.contentPage h1 {
        padding-top: 35px;
    }

    #app.desktop div.container {
        display: inline;
    }

    div.contentPage div.col8 {
        padding-left: 30px;
        padding-right: 30px;
    }

    div.contentPage div.col4 {
        padding-left: 30px;
        padding-right: 30px;
    }

    div.contentPage h1 {
        text-align: left;
        font-weight: Bold;
        font-size: 2.25rem;
        line-height: 2.813rem;
        letter-spacing: 0;
        color: #003063;
        opacity: 1;
    }

    div.contentPage hr {
        border-color: black;
    }

    div.contentPage div.metaline {
        margin-bottom: 30px;
        text-align: right;
    }

    div.contentPage div.metaline > div {
        display: inline-block;
    }

    div.contentPage div.metaline div.rating {
        width: 225px;
    }
</style>
