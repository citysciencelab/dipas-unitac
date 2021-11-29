/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The dipas rating widget component.
 * @displayName RatingWidget
 */
import _ from "underscore";
import {requestBroker} from "../mixins/requestBroker.js";
import DipasButton from "./DipasButton.vue";

export default {
  /**
   * More Informations for the RatingWidget is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "RatingWidget",
  components: {
    DipasButton
  },
  mixins: [requestBroker],
  props: {
    /**
     * Type of the entity.
     * @name entityType
     */
    entityType: {
      type: String,
      default: ""
    },
    /**
     * ID for the entity.
     * @name entityID
     */
    entityID: {
      type: String,
      default: ""
    },
    /**
     * ToDo
     * @name bundle
     */
    bundle: {
      type: String,
      default: ""
    },
    /**
     * the rating with upVotes and downVotes.
     * @name rating
     */
    rating: {
      type: Object,
      default () {
        return {
          upVotes: 0,
          downVotes: 0
        };
      }
    },
    /**
     * The style object for rating widget
     * @name widgetStyle
     */
    widgetStyle: {
      type: String,
      default: "full"
    },
    /**
     * Allows to rate for a contibution.
     * @name ratingsAllowed
     * @values true, false
     */
    ratingsAllowed: {
      type: Boolean
    }
  },
  data () {
    return {
      cookieData: this.$cookies.get("dipas"),
      savingInProgress: false
    };
  },
  computed: {
    /**
     * serves wether the cookies are accepted or not
     * @name cookiesAccepted
     * @returns {Boolean}
     */
    cookiesAccepted () {
      return !_.isNull(this.cookieData) || this.$root.cookieBannerConfirmed;
    },
    /**
     * serves the rating widget display style
     * @name displayStyle
     * @returns {String}
     */
    displayStyle () {
      return this.widgetStyle;
    },
    /**
     * serves the amount of up votes
     * @name upVotes
     * @returns {Number} upVotes
     */
    upVotes: {
      get () {
        return Number(this.rating.upVotes);
      },
      set (val) {
        this.$parent.contribution.rating.upVotes = val;
      }
    },
    /**
     * serves the amount of down votes
     * @name downVotes
     * @returns {Number} downVotes
     */
    downVotes: {
      get () {
        return Number(this.rating.downVotes);
      },
      set (val) {
        this.$parent.contribution.rating.downVotes = val;
      }
    },
    /**
     * serves the amount of all votes
     * @name allVotes
     * @returns {Number} allVotes
     */
    allVotes () {
      return this.upVotes + this.downVotes;
    },
    /**
     * serves the the graphical bar up with value as css style percent
     * @name upWidth
     * @returns {String} upWidth
     */
    upWidth () {
      let percent = this.allVotes > 0 ? 100 * this.upVotes / this.allVotes : 0;

      if (this.allVotes > 0 && (percent === 100 || percent === 0)) {
        percent = percent === 100 ? 99 : 1;
      }
      return "width: " + percent + "%;";
    },
    /**
     * serves the the graphical bar down width value as css style percent
     * @name downWidth
     * @returns {String} downWidth
     */
    downWidth () {
      let percent = this.allVotes > 0 ? 100 * this.downVotes / this.allVotes : 0;

      if (this.allVotes > 0 && (percent === 100 || percent === 0)) {
        percent = percent === 100 ? 99 : 1;
      }
      return "width: " + percent + "%;";
    },
    /**
     * serves wether the client has already voted or not
     * @name hasVoted
     * @returns {Boolean} hasVoted
     */
    hasVoted () {
      if (
        !_.isNull(this.cookieData) &&
                    !_.isUndefined(this.cookieData.votes) &&
                    !_.isUndefined(this.cookieData.votes[this.entityType]) &&
                    _.isArray(this.cookieData.votes[this.entityType]) &&
                    this.cookieData.votes[this.entityType].indexOf(this.entityID) !== -1
      ) {
        return true;
      }
      return false;
    },
    /**
     * serves wether the client can vote or not
     * @name canVote
     * @returns {Boolean} canVote
     */
    canVote () {
      return this.cookiesAccepted && !this.hasVoted && !this.savingInProgress;
    },
    /**
     * serves wether tooltip is disabled or not
     * @name disabledTooltip
     * @returns {Boolean} disabledTooltip
     */
    disabledTooltip () {
      return !_.isNull(this.cookieData)
        ? this.$t("RatingWidget.already")
        : this.$t("RatingWidget.acceptedCookies");
    }
  },
  methods: {
    /**
     * UpVote the rating 1 step
     * @returns {void}
     */
    voteUp: function () {
      this.savingInProgress = true;
      this.addRating({
        id: this.entityID,
        rating: 1
      });
      this.invalidateCache();
    },
    /**
     * DownVote the rating 1 step
     * @returns {void}
     */
    voteDown: function () {
      this.savingInProgress = true;
      this.addRating({
        id: this.entityID,
        rating: -1
      });
      this.invalidateCache();
    },
    invalidateCache: function () {
      this.$store.commit("invalidateStateCache", "rating:" + this.bundle + ":" + this.entityID);
    }
  }
};
</script>

<template>
  <div :class="['ratingwidget', displayStyle]">
    <div
      v-if="displayStyle === 'full' && ratingsAllowed"
      class="ratingActions"
    >
      <!--
        triggered on click
        @event click
      -->
      <DipasButton
        class="angular lightgreen rateButton"
        :disabled="!canVote"
        :disabledText="disabledTooltip"
        icon="thumb_up"
        :aria-label="$t('RatingWidget.iconVoteThumbUp')"
        @click="voteUp"
        @keyup.enter="voteUp"
      />
      <!--
        triggered on click
        @event click
      -->
      <DipasButton
        class="angular lightred rateButton"
        :disabled="!canVote"
        :disabledText="disabledTooltip"
        icon="thumb_down"
        :aria-label="$t('RatingWidget.iconVoteThumbDown')"
        @click="voteDown"
        @keyup.enter="voteDown"
      />
    </div>

    <div class="ratingStatus">
      <div class="thumbs upVotes">
        {{ upVotes }}
        <i
          :aria-label="$t('RatingWidget.iconThumbUp')"
          class="material-icons"
        >
          thumb_up
        </i>
      </div>

      <div
        class="barChart"
        :class="{noVotes: !allVotes}"
      >
        <div
          v-if="displayStyle === 'full'"
          class="bar green"
          :style="upWidth"
        >
        </div>

        <div
          v-if="displayStyle === 'full'"
          class="bar red"
          :style="downWidth"
        >
        </div>
      </div>

      <div class="thumbs downVotes">
        <i
          :aria-label="$t('RatingWidget.iconThumbDown')"
          class="material-icons"
        >
          thumb_down
        </i>
        {{ downVotes }}
      </div>
    </div>
  </div>
</template>

<style>
    div.ratingwidget div.ratingActions {
        display: flex;
        justify-content: space-between;
    }

    div.ratingwidget div.ratingActions button.rateButton {
        display: inline-block;
        width: 90px;
        height: 40px;
    }

    div.ratingwidget div.ratingActions button.rateButton:focus-visible {
        outline: 3px solid #005CA9;
        outline-offset: -4px;
    }

    div.ratingwidget div.ratingStatus {
        width: 100%;
        display: flex;
        margin-top: 10px;
        padding-right: 5px;
    }

    div.ratingwidget.simple div.ratingStatus {
        position: relative;
        top: -4px;
    }

    div.ratingwidget div.ratingStatus > div {
        display: inline-block;
        cursor: default;
    }

    div.ratingwidget div.ratingStatus div.thumbs {
        font-size: 0.75rem;
        line-height: 10px;
        display: inline-block;
        white-space: nowrap;
    }

    div.ratingwidget div.ratingStatus div.thumbs.upVotes {
        text-align: right;
        margin-right: 2px;
    }

    div.ratingwidget div.ratingStatus div.thumbs.downVotes {
        text-align: left;
        margin-left: 2px;
    }

    div.ratingwidget.simple div.ratingStatus div.thumbs {
        font-size: 0.8rem;
    }

    div.ratingwidget div.ratingStatus div.thumbs i.material-icons {
        transform: scaleX(-1);
        line-height: 10px;
        font-size: 1.187rem;
        position: relative;
    }

    div.ratingwidget.simple div.ratingStatus div.thumbs i.material-icons {
        font-size: 1.187rem;
    }

    div.ratingwidget div.ratingStatus div.thumbs.upVotes i.material-icons {
        top: 3px
    }

    div.ratingwidget div.ratingStatus div.thumbs.downVotes i.material-icons {
        top: 8px
    }

    div.ratingwidget.simple div.ratingStatus div.thumbs.downVotes i.material-icons {
        top: 7px;
    }

    div.ratingwidget div.ratingStatus div.barChart {
        height: 9px;
        white-space: unset;
        flex-grow: 1;
        position: relative;
        top: -2px;
    }

    div.ratingwidget div.ratingStatus div.barChart.noVotes {
        background-color: lightgrey;
        top: 6px;
    }

    div.ratingwidget.simple div.ratingStatus div.barChart.noVotes {
        background-color: transparent;
    }

    div.ratingwidget div.ratingStatus div.barChart div.bar {
        display: inline-block;
        height: 100%;
    }

    div.ratingwidget div.ratingStatus div.barChart div.bar.green {
        background-color: #107C10;
    }

    div.ratingwidget div.ratingStatus div.barChart div.bar.red {
        background-color: #E10019;
    }

    div.ratingwidget div.ratingActions .lightgreen .material-icons {
        transform: scaleX(-1);
        font-size: 1.25rem;
        margin: 0px 1px 2px 0px;
    }

    div.ratingwidget div.ratingActions .lightred .material-icons {
        transform: scaleX(-1);
        font-size: 1.25rem;
        margin: 5px 0px 0px 7px;
    }
</style>
