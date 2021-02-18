/**
 * @license GPL-2.0-or-later
 */

<script>
import moment from "moment";
import CommentsForm from "./CommentsForm.vue";

export default {
  name: "CommentsListComment",
  components: {
    CommentsForm
  },
  props: {
    rootEntityBundle: undefined,
    rootEntityID: {
      type: String,
      default: ""
    },
    parentType: {
      type: String,
      default: ""
    },
    parent: {
      type: Object,
      default () {
        return {};
      }
    },
    comment: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      formID: "commentsform-comment-" + (this.parent.cid ? this.parent.cid : this.comment.cid),
      showForm: false,
      commentFormHeadline: this.$t("CommentsList.Comment.reply"),
      commentSubject: "",
      cssCommentClasses: [
        "commentWrapper",
        this.parentType === "node" ? "comment" : "reply"
      ]
    };
  },
  computed: {
    created () {
      return moment(this.comment.created).format("DD.MM.YYYY | HH:mm:ss") + " Uhr";
    },
    commentsOpen () {
      return this.$store.getters[this.rootEntityBundle + "CommentsState"] === "open";
    }
  },
  created () {
    this.$root.$on("highlightComment", (id) => this.$emit("highlight", id));
    this.$on("highlight", function (id) {
      if (id === this.comment.cid) {
        this.$scrollTo(
          "#comment-" + id,
          300,
          {
            container: "section.content",
            onDone: function () {
              this.cssCommentClasses.push("highlighted");
              setTimeout(function (comp) {
                comp.cssCommentClasses.splice(comp.cssCommentClasses.length - 1, 1);
              }, 400, this);
            }.bind(this)
          }
        );
      }
    });
    this.$root.$on("hideCommentForm", (id) => this.$emit("hideForm", id));
    this.$on("hideForm", function (id) {
      if (id !== this.comment.cid) {
        this.showForm = false;
      }
    });
    this.$root.$on("resetCommentForms", () => this.$emit("resetForm"));
    this.$on("resetForm", function () {
      this.showForm = false;
    });
  },
  methods: {
    addComment: function (entityID) {
      if (!this.showForm) {
        this.commentFormHeadline = this.$t("CommentsList.Comment.replyNoPlural") + entityID;
        this.commentSubject = this.$t("CommentsList.Comment.replyNo") + entityID;
        this.$root.$emit("hideCommentForm", this.comment.cid);
        this.$nextTick(() => this.$scrollTo("#" + this.formID, 200, {container: "section.content"}));
      }
      else {
        this.$root.$emit("showCommentForm");
      }
      this.showForm = !this.showForm;
    },
    highlightComment: function (subject) {
      this.$root.$emit("highlightComment", subject.replace(/^\D+/g, ""));
    }
  }
};
</script>

<template>
  <div
    :id="'comment-' + comment.cid"
    :class="cssCommentClasses"
  >
    <p class="text">
      <span
        v-if="comment.subject"
        class="subject"
        @click="highlightComment(comment.subject)"
      >
        {{ comment.subject }}:
      </span>

      {{ comment.comment }}
    </p>

    <div class="subline">
      <p class="meta">
        {{ $t("CommentsList.Comment.meta", {"created": created, "commentCid": comment.cid}) }}
      </p>
      <p
        v-if="commentsOpen"
        class="addComment"
        @click="addComment(comment.cid)"
      >
        {{ !showForm ? $t("CommentsList.Comment.comment") : $t("CommentsList.Comment.cancel") }}
      </p>
    </div>

    <CommentsForm
      v-if="commentsOpen && showForm"
      :formID="formID"
      :headline="commentFormHeadline"
      commentedEntityType="comment"
      :commentedEntityID="parent.cid ? parent.cid : comment.cid"
      :commentSubject="commentSubject"
      :rootEntityID="rootEntityID"
      @reloadComments="$emit('reloadComments', $event)"
    />

    <div
      v-if="comment.replies.length"
      class="replies"
    >
      <CommentsListComment
        v-for="reply in comment.replies"
        :key="reply.cid"
        :comment="reply"
        parentType="comment"
        :parent="comment"
        :rootEntityBundle="rootEntityBundle"
        :rootEntityID="rootEntityID"
        @reloadComments="$emit('reloadComments', $event)"
      />
    </div>
  </div>
</template>

<style>
    div.comment,
    div.reply {
        padding: 30px 30px 10px;
        transition: .5s;
    }

    div.comment.highlighted,
    div.reply.highlighted {
        background-color: #8badd5;
    }

    div.reply {
        padding-bottom: 0px;
    }

    div.comment {
        background-color: #F0F0F0;
        margin-bottom: 20px;
        word-break: break-word;
    }

    div.reply {
        background-color: #E3E3E3;
        border-top: solid 1px #707070;
    }

    div.reply p.text span.subject {
        color: #005CA9;
        cursor: pointer;
    }

    div.replies div.reply:last-child {
        border-bottom: solid 1px #707070;
        margin-bottom: 20px;
    }

    div.commentWrapper div.subline p {
        display: inline-block;
        width: 50%;
    }

    div.commentWrapper div.subline p.meta {
        font-size: 13px;
    }

    div.commentWrapper div.subline p.addComment {
        color: #005CA9;
        text-decoration: underline;
    }

    div.commentWrapper div.subline p.addComment {
        text-align: right;
        cursor: pointer;
    }
</style>
