/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The form for commenting the contribution
 * @displayName CommentsForm
 */
import {requestBroker} from "../../../mixins/requestBroker.js";

export default {
  name: "CommentsForm",
  mixins: [requestBroker],
  props: {
    /**
     * form ID
     */
    formID: {
      type: String,
      default: ""
    },
    /**
     * root entity bundle
     */
    rootEntityBundle: {
      type: String,
      default: ""
    },
    /**
     * id of root entity
     */
    rootEntityID: {
      type: String,
      default: ""
    },
    /**
     * commented entity type
     */
    commentedEntityType: {
      type: String,
      default: ""
    },
    /**
     * commented entity ID
     */
    commentedEntityID: {
      type: String,
      default: ""
    },
    /**
     * form headline
     */
    headline: {
      type: String,
      default: ""
    },
    /**
     * the subject of the comment
     */
    commentSubject: {
      type: String,
      default: ""
    }
  },
  data () {
    return {
      id: "commentform-" + this.commentedEntityType + "-" + this.commentedEntityID,
      saving: false,
      canceled: false,
      comment: "",
      mobileurl: "",
      commentMinLength: 5,
      tooShort: false,
      rndid: "id" + Math.floor(Math.random() * 100000000)
    };
  },
  computed: {
    /**
     * serves the maximum length of the comment
     * @returns {Number} max length of a comment
     */
    maxlength () {
      return this.$store.getters.commentMaxlength;
    },
    /**
     * serves the remaining chars of the comment
     * @returns {Number} remaining chars befor reach the max length of comment
     */
    remaining () {
      return this.maxlength - this.comment.length;
    }
  },
  methods: {
    /**
     * scrolls the page to the active form
     * @returns {void}
     */
    scrollToForm: function () {
      this.$scrollTo("#" + this.formID, 300, {container: "section.content"});
    },
    /**
     * saves the comment object to the vuex store if length enough
     * @returns {void}
     */
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
        const currTextarea = this.$refs.txtarea;

        currTextarea.classList.add("error");
        setTimeout(function (comp) {
          comp.tooShort = false;
          currTextarea.classList.remove("error");
        }, 3000, this);
      }
    },
    cancelComment: function () {
      this.canceled = true;
      setTimeout(function (context) {
        context.canceled = false;
        context.comment = "";
        context.$root.$emit("resetCommentForms");
      }, 3000, this);
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
      <label
        :for="rndid"
        class="sr-only"
      >
        {{ headline }}
      </label>
      <!--
        @fires focus scroll to form
        @model comment
        @property {String} maxlength
      -->
      <textarea
        :id="rndid"
        ref="txtarea"
        v-model="comment"
        class="comment"
        aria-describedby="tooshorthint cancelhint charsremaining"
        :maxlength="maxlength"
        @focus="scrollToForm"
      />
      <div class="additional">
        <!--
          @model mobileurl
        -->
        <input
          v-model="mobileurl"
          type="text"
        />
      </div>
      <div class="subline">
        <p
          id="charsremaining"
          class="counter"
          role="status"
        >
          {{ $t("CommentsForm.remainingChars", {"remaining": remaining}) }}
          <span
            v-if="tooShort"
            id="tooshorthint"
            role="alert"
          >
            {{ $t("CommentsForm.tooShort") }}
          </span>
          <span
            v-if="canceled"
            id="cancelhint"
            role="alert"
          >
            {{ $t("CommentsForm.canceled") }}
          </span>
        </p>
        <p
          id="buttons"
          class="button"
        >
          <span>
            <button
              class="cancel"
              tabindex="0"
              @click="cancelComment"
            >
              {{ $t("CommentsForm.cancelComment") }}
            </button>
            <button
              tabindex="0"
              @click="saveComment"
            >
              {{ $t("CommentsForm.submitComment") }}
            </button>
          </span>
        </p>
      </div>
    </template>
    <template v-else-if="canceled">
      <p
        v-if="canceled"
        role="alert"
      >
        {{ $t("CommentsForm.canceled") }}
      </p>
    </template>
    <template v-else>
      <p
        role="alert"
      >
        {{ $t("CommentsForm.saved") }}
      </p>
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
        font-size: 1.125rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    section.commentform textarea.comment {
        width: 100%;
        height: 200px;
        margin: 0;
        border: solid 1px black;
    }

    section.commentform textarea.comment.error {
        border: solid 1px #E10019;
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
        font-size: 0.875rem;
        color: #595959;
    }

    section.commentform div.subline p.button {
        margin: 0;
        text-align: right;
    }

    section.commentform div.subline p.button span {
        position: relative;
        display: block;
        margin-right: 5px;
        margin-bottom:10px;
    }

    section.commentform div.subline p.button button {
        margin: 0 10px 0 0;
        padding: 5px 15px;
        border: none 0 transparent;
        background-color: #E10019;
        color: white;
        position: relative;
        font-size: 1rem;
    }


    section.commentform div.subline p.button button.cancel {
        margin-right: 10px;
        margin-bottom: 5px;
        background-color: transparent;
        color: #005CA9;
        padding: 0;
        font-weight: bold;
    }

    section.commentform div.subline span#cancelhint,
    section.commentform div.subline span#tooshorthint {
      display: block;
      color: #E10019;
    }

    section.commentform div.subline p#buttons {
      width: 70%
    }
    section.commentform div.subline p#charsremaining {
      width: 30%;
    }
    @media (min-width: 480px) {
      section.commentform div.subline p#buttons {
        width: 50%
      }
      section.commentform div.subline p#charsremaining {
        width: 50%;
      }
    }

    section.commentform div.subline p.button button:focus:not(:focus-visible) {
        outline: none;
    }

    section.commentform div.subline p.button button:focus-visible {
        outline: 3px solid #005CA9;
        outline-offset: 4px;
    }
    </style>
