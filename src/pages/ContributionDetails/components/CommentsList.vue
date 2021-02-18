/**
 * @license GPL-2.0-or-later
 */

<script>
import {requestBroker} from "../../../mixins/requestBroker.js";
import CommentsListComment from "./CommentsListComment.vue";

export default {
  name: "CommentsList",
  components: {
    CommentsListComment
  },
  mixins: [requestBroker],
  props: {
    rootEntityBundle: undefined,
    rootEntityID: {
      type: String,
      default: ""
    },
    contributionID: {
      type: String,
      default: ""
    },
    contribution: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      commentsTimestamp: new Date().getTime(),
      commentcount: 0,
      comments: []
    };
  },
  watch: {
    // This watch only gets triggered if the parent node is not cached.
    contributionID () {
      this.loadComments(this.contributionID);
    }
  },
  created () {
    // Since this component is dependent on an ajax request, the property
    // might be undefined on first load.
    if (this.contributionID !== "") {
      this.loadComments(this.contributionID);
    }
  }
};
</script>

<template>
  <section class="comments">
    <div class="oneline">
      <p class="headline">
        {{ $t("CommentsList.headline") }}
      </p>
      <p class="commentcount">
        {{ $t("CommentsList.commentCount", {"commentcount": commentcount}) }}
      </p>
    </div>

    <p v-if="!comments.length">
      {{ $t("CommentsList.noComments") }}
    </p>

    <div
      :key="'comments-' + commentsTimestamp"
      class="comments"
    >
      <CommentsListComment
        v-for="comment in comments"
        :key="comment.cid"
        :comment="comment"
        :rootEntityBundle="rootEntityBundle"
        :rootEntityID="rootEntityID"
        parentType="node"
        :parent="contribution"
        @reloadComments="$emit('reloadComments', $event)"
      />
    </div>
  </section>
</template>

<style>
    div.oneline p {
        display: inline-block;
        width: 50%;
    }

    div.oneline p.headline {
        color: #003063;
        font-size: 26px;
        font-weight: bold;
    }

    div.oneline p.commentcount {
        text-align: right;
    }
</style>
