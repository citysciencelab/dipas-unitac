/**
 * @license GPL-2.0-or-later
 */

<script>
import {requestBroker} from "../../../mixins/requestBroker.js";

export default {
  name: "CommentsForm",
  mixins: [requestBroker],
  props: {
    formID: {
      type: String,
      default: ""
    },
    rootEntityBundle: {
      type: String,
      default: ""
    },
    rootEntityID: {
      type: String,
      default: ""
    },
    commentedEntityType: {
      type: String,
      default: ""
    },
    commentedEntityID: {
      type: String,
      default: ""
    },
    headline: {
      type: String,
      default: ""
    },
    commentSubject: {
      type: String,
      default: ""
    }
  },
  data () {
    return {
      id: "commentform-" + this.commentedEntityType + "-" + this.commentedEntityID,
      saving: false,
      comment: "",
      mobileurl: "",
      commentMinLength: 5,
      tooShort: false
    };
  },
  computed: {
    maxlength () {
      return this.$store.getters.commentMaxlength;
    },
    remaining () {
      return this.maxlength - this.comment.length;
    }
  },
  methods: {
    scrollToForm: function () {
      this.$scrollTo("#" + this.formID, 300, {container: "section.content"});
    },
    saveComment: function () {
      if (this.comment.length >= this.commentMinLength && this.mobileurl.length === 0) {
        this.saving = true;
        this.addComment({
          rootEntityID: this.rootEntityID,
          commentedEntityType: this.commentedEntityType,
          commentedEntityID: this.commentedEntityID,
          subject: this.commentSubject,
          comment: this.comment
        });
        this.$store.commit("invalidateStateCache", "comment:" + this.rootEntityBundle + ":" + this.rootEntityID);
      }
      else if (this.comment.length < this.commentMinLength) {
        this.tooShort = true;
        setTimeout(function (comp) {
          comp.tooShort = false;
        }, 1000, this);
      }
    }
  }
};
</script>

<template>
  <section
    :id="formID"
    class="commentform"
  >
    <template v-if="!saving">
      <p class="headline">
        {{ headline }}
      </p>
      <textarea
        v-model="comment"
        class="comment"
        :maxlength="maxlength"
        @focus="scrollToForm"
      />
      <div class="additional">
        <input
          v-model="mobileurl"
          type="text"
        />
      </div>
      <div class="subline">
        <p class="counter">
          {{ $t("CommentsForm.remainingChars", {"remaining": remaining}) }}
        </p>
        <p class="button">
          <span v-if="tooShort">{{ $t("CommentsForm.tooShort") }}</span>
          <button @click="saveComment">
            {{ $t("CommentsForm.submitComment") }}
          </button>
        </p>
      </div>
    </template>
    <template v-else>
      <p>{{ $t("CommentsForm.saved") }}</p>
    </template>
  </section>
</template>

<style>
    section.commentform {
        margin-top: 20px;
        margin-bottom: 20px;
    }

    section.commentform p.headline {
        color: #003063;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    section.commentform textarea.comment {
        width: 100%;
        height: 200px;
        margin: 0;
        border: solid 1px black;
    }

    section.commentform div.additional {
        height: 0;
        margin: 0;
        visibility: hidden;
    }

    section.commentform div.subline {
        margin: 0;
    }

    section.commentform div.subline p {
        display: inline-block;
        width: 50%;
        vertical-align: top;
    }

    section.commentform div.subline p.counter,
    section.commentform div.subline p.button span {
        line-height: 20px;
        font-size: 14px;
        color: #767676;
    }

    section.commentform div.subline p.button {
        margin: 0;
        text-align: right;
    }

    section.commentform div.subline p.button span {
        position: relative;
        top: -7px;
        display: inline-block;
        margin-right: 5px;
    }

    section.commentform div.subline p.button button {
        margin: 0;
        padding: 5px 15px;
        border: none 0 transparent;
        background-color: #E10019;
        color: white;
        position: relative;
        top: -7px;
    }

    section.commentform div.subline p.button button:focus {
        outline: none;
    }
</style>
