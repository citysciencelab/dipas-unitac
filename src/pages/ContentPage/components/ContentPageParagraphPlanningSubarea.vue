/**
 * @license GPL-2.0-or-later
 */

<script>
import ContentPageParagraphConception from "./ContentPageParagraphConception.vue";

export default {
  name: "ContentPageParagraphPlanningSubarea",
  components: {
    ContentPageParagraphConception
  },
  props: {
    content: {
      type: Object,
      default () {
        return {};
      }
    },
    showHeadline: {
      type: Boolean
    }
  }
};
</script>

<template>
  <div class="planningSubarea">
    <p
      v-if="showHeadline"
      class="headline"
    >
      {{ content.field_name }}
    </p>

    <div class="row">
      <ContentPageParagraphConception
        v-for="(element, index) in content.field_content"
        :key="index"
        :content="element.field_conception"
        class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-xs-3"
      />
    </div>
  </div>
</template>

<style>
    div.planningSubarea {
        margin: 20px 0;
    }

    div.planningSubarea > p.headline {
        font-size: 24px;
        line-height: 24px;
        font-weight: bold;
        letter-spacing: 0;
    }
</style>
