/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the frame for the survey page
 * @displayName SurveyPage
 */
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "SurveyPage",
  extends: ContentPage,
  computed: {
    /**
     * @name url
     * @returns {String} url
     */
    url () {
      return this.$store.getters.surveyLink;
    },
    /**
     * @name linkParagraph
     * @returns {String} link paragraph
     */
    linkParagraph () {
      return "<p>" + this.$t("Survey.linkPrefix") + ": <a href='" + this.url + "' target='_blank'>" + this.$t("Survey.linkText") + "</a></p>";
    },
    /**
     * @name staticContent
     * @returns {Object} survey content data
     */
    staticContent () {
      return {
        title: this.$t("Survey.title"),
        content: [
          {
            type: "paragraph",
            bundle: "text",
            field_text: this.linkParagraph
          },
          {
            type: "paragraph",
            bundle: "text",
            field_text: "<p>" + this.$t("Survey.disclaimer") + "</p>"
          }
        ]
      };
    }
  }
};
</script>
