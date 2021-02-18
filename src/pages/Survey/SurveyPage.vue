/**
 * @license GPL-2.0-or-later
 */

<script>
import ContentPage from "../ContentPage/ContentPage.vue";

export default {
  name: "SurveyPage",
  extends: ContentPage,
  computed: {
    url () {
      return this.$store.getters.surveyLink;
    },
    linkParagraph () {
      return "<p>" + this.$t("Survey.linkPrefix") + ": <a href='" + this.url + "' target='_blank'>" + this.$t("Survey.linkText") + "</a></p>";
    },
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
