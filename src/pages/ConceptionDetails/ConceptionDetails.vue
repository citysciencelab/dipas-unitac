/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the conception detail data and comments
 * @displayName ConceptionDetails
 */
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";
import ConceptionDetailsRightColumn from "./components/ConceptionDetailsRightColumn.vue";

export default {
  name: "ConceptionDetails",
  components: {
    ConceptionDetailsRightColumn
  },
  extends: ContentPage,
  mixins: [requestBroker],
  props: {
    /**
     * The ID of the conception
     * @type {String} id
     */
    id: {
      type: String,
      default: ""
    }
  },
  data () {
    return {
      showRatingWidget: false, // Currently unavailable.
      commentsOpen: this.$store.getters.conceptionCommentsState === "open",
      showForm: true,
      showCommentList: this.$store.getters.displayConceptionComments,
      ratingsAllowed: false,
      commentsFormHeadline: this.$t("ConceptionDetails.commentsFormHeadline"),
      RightColumn: "ConceptionDetailsRightColumn"
    };
  },
  beforeMount () {
    /**
     * Loads conception data via request broker
     * @param {String} id
     * @returns {void}
     */
    this.loadConception(this.id);
  },
  mounted () {
    /**
     * watch the store changes
     * @returns {void}
     */
    this.$store.watch(
      function () {
        return this.$store.getters.conceptionCommentsState;
      }.bind(this),
      (val) => {
        this.commentsOpen = val === "open";
      }
    );
    /**
     * watch the store changes
     * @returns {void}
     */
    this.$store.watch(
      function () {
        return this.$store.getters.displayConceptionComments;
      }.bind(this),
      (val) => {
        this.showCommentList = val;
      }
    );
  }
};
</script>

<style>
  .textParagraph a {
    color: #005CA9;
    font-weight: bold;
  }
</style>
