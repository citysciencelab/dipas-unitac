/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Holds the content for the paragraph planning subarea
 * @displayName ContentPageParagraphPlanningSubarea
 */
import ContentPageParagraphConception from "./ContentPageParagraphConception.vue";

export default {
  name: "ContentPageParagraphPlanningSubarea",
  components: {
    ContentPageParagraphConception
  },
  props: {
    /**
     * serves the content object
     */
    content: {
      type: Object,
      default () {
        return {};
      }
    },
    /**
     * serves the boolean to show or hide the headline
     */
    showHeadline: {
      type: Boolean
    }
  }
};
</script>

<template>
  <div class="planningSubarea">
    <h2
      v-if="showHeadline"
      class="headline"
    >
      {{ content.field_name }}
    </h2>

    <div class="row">
      <!--
        ContentPageParagraphConception component
        @property {Object} content
      -->
      <ContentPageParagraphConception
        v-for="(element, index) in content.field_content"
        :key="index"
        :content="element.field_conception"
        class="col-xl-5 col-lg-5 col-md-5 col-sm-5 col-xs-5"
      />
    </div>
  </div>
</template>

<style>
    div.planningSubarea {
        margin: 40px 0;
    }

    div.planningSubarea > h2.headline {
        font-size: 1.875rem;
        line-height: 1.875rem;
        font-weight: bold;
        letter-spacing: 0;
    }

    div.planningSubarea div.row {
      align-items: flex-end;
    }
</style>
