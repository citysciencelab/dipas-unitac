/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * The  contribution list component.
 * @displayName ContributionList
 */

import _ from "underscore";
import moment from "moment";
import JSum from "jsum";
import {mapGetters} from "vuex";
import {requestBroker} from "../../mixins/requestBroker.js";
import ContributionListTeaser from "./components/ContributionListTeaser.vue";
import DipasButton from "../../basicComponents/DipasButton.vue";
import ContributionListFilter from "./components/ContributionListFilter.vue";

export default {
  /**
   * More Information for the contribution list is described here.
   *
   * @example ./doc/documentation.md
   */
  name: "ContributionList",
  components: {
    ContributionListTeaser,
    DipasButton,
    ContributionListFilter
  },
  mixins: [requestBroker],
  data () {
    return {
      text: "",
      loading: true,
      showNodes: false,
      itemsPerPage: 10,
      listDefaults: {
        filters: {
          category: [],
          rubric: []
        },
        sort: {
          field: "created",
          direction: "DESC"
        }
      },
      filterSettings: {},
      contributionList: {},
      componentInitialized: false
    };
  },
  computed: {
    ...mapGetters([
      "projectperiod",
      "projectRunning",
      "takesNewContributions",
      "frontpage"
    ]),
    /**
     * @name projectStarted
     * @returns {String} date of project start
     */
    projectStarted () {
      return moment().utc() >= moment(this.projectperiod.start);
    },
    /**
     * @name projectEnded
     * @returns {String} date of project end
     */
    projectEnded () {
      return moment().utc() > moment(this.projectperiod.end);
    },
    /**
     * @name filterHash
     * @returns {String} SHA256 styled hash value
     */
    filterHash () {
      return JSum.digest(this.filterSettings, "SHA256", "hex");
    },
    /**
     * @name filteraApplied
     * @returns {Number} rubric or category length
     */
    filtersApplied () {
      return this.filterSettings.filters.category.length || this.filterSettings.filters.rubric.length;
    }
  },
  created () {
    /**
     * shows load more... if the bottom is reached
     * @fire scrollBottomReached
     * @returns {void}
     */
    this.$root.$on("scrollBottomReached", () => {
      if (this.$route.fullPath === "/contributionlist" ||
        (this.$route.fullPath === "/" && this.frontpage === "contributionlist")) {
        this.loadMore();
      }
    });
    /**
     * @fire routeChange
     * @returns {void}
     */
    this.$root.$on("routeChange", function (change) {
      if (!(
        (change.from.fullPath === "/contributionlist" && (/^\/contribution\/\d+$/).test(change.to.fullPath))
                    ||
                    ((/^\/contribution\/\d+$/).test(change.from.fullPath) && change.to.fullPath === "/contributionlist")
      )) {
        this.initializeListing();
      }
    }.bind(this));
  },
  beforeMount () {
    this.initializeListing();
    this.$nextTick(function () {
      this.componentInitialized = true;
    }.bind(this)());
  },
  activated () {
    if (this.componentInitialized) {
      this.initializeListing();
    }
  },
  methods: {
    /**
     * Initializes the listing of the contributions.
     * @returns {void}
     */
    initializeListing: function () {
      this.filterSettings = _.clone(this.listDefaults);
      this.loadContributionList({
        page: 1,
        itemsPerPage: this.itemsPerPage,
        filters: this.filterSettings.filters,
        sort: this.filterSettings.sort
      });
    },
    /**
     * Loads more contributions to the page.
     * @returns {void}
     */
    loadMore: function () {
      if (!this.contributionList.pager.lastPage && !this.loading) {
        const options = {
          page: this.contributionList.pager.currentPage + 1,
          itemsPerPage: this.itemsPerPage,
          filters: this.filterSettings.filters,
          sort: this.filterSettings.sort
        };

        this.loading = true;
        this.loadContributionList(options);
      }
    },
    /**
     * Applys the filter according to the filter settings.
     * @returns {void}
     */
    applyFilters: function (filterConfig) {

      this.filterSettings = filterConfig;
      this.showNodes = false;
      this.loading = true;
      this.loadContributionList({
        page: 1,
        itemsPerPage: this.itemsPerPage,
        filters: filterConfig.filters,
        sort: filterConfig.sort
      });
      this.$root.showFilter = false;
    },
    /**
     * Removes the filter settings.
     * @returns {void}
     */
    cancel: function () {
      this.$root.showFilter = false;
    }
  }
};
</script>

<template>
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7 col-xl-7">
        <h1>{{ $t('ContributionList.title') }}</h1>

        <p
          role="status"
        >
          {{ $t('ContributionList.itemCount') }}: {{ contributionList.totalNodes }}
          <span
            v-if="filtersApplied"
          >
            ({{ $t('ContributionList.filtered') }})
          </span>
        </p>

        <div
          v-if="showNodes"
          class="row teaserlist"
        >
          <p
            v-if="!contributionList.nodes.length"
            class="col-xs-12 col-12 noContributions"
            role="status"
          >
            <template v-if="projectRunning && filtersApplied">
              {{ $t('ContributionList.noContributionsForCriteria') }}
            </template>
            <template v-if="projectRunning && !filtersApplied">
              {{ $t('ContributionList.writeYourFirst') }}
            </template>
            <template v-if="!projectRunning && !projectStarted">
              {{ $t('ContributionList.notBeenStarted') }}
            </template>
            <template v-if="!projectRunning && projectEnded">
              {{ $t('ContributionList.endsWithoutContributions') }}
            </template>
          </p>
          <!--
            @name ContributionListTeaser
            @property {Object} contribution list
          -->
          <ContributionListTeaser
            v-for="contribution in contributionList.nodes"
            :key="contribution.nid"
            :teaser="contribution"
            class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xl-6"
          />

          <p
            v-if="!loading && !contributionList.pager.lastPage"
            class="col-xs-12 col-12 loadMore"
            @click="loadMore"
          >
            <i
              aria-hidden="true"
              class="material-icons"
            >
              list
            </i>
            {{ $t('ContributionList.loadMore') }}
          </p>

          <p
            v-if="$root.isMobile && contributionList.pager.lastPage"
            class="col-xs-12 col-12 noMoreEntries"
          >
            {{ $t('ContributionList.noContributions') }}
          </p>
        </div>

        <div
          v-if="loading"
          class="d-flex justify-content-center spinner"
        >
          <div
            class="spinner-border"
            role="status"
          >
            <span class="sr-only">{{ $t('ContributionList.ContributionListTeaser.loading') }}</span>
          </div>
        </div>
      </div>

      <div class="col-md-1 col-1"></div>

      <div
        v-if="!$root.isMobile"
        class="col-md-4 col-4 sidebar"
      >
        <!--
          triggered on click
          @event click
        -->
        <div class="inner">
          <!--
            @name Dipas Button
            @event click createContribution
          -->
          <DipasButton
            v-if="projectRunning && takesNewContributions"
            :text="$t('ContributionList.addNew')"
            class="red round"
            icon="add"
            @click="$root.$emit('createContribution')"
          />
          <!--
            @name ContributionListFilter
            @fire filter apply the filters
            @fire resetFilters
          -->
          <ContributionListFilter
            :key="filterHash"
            :filtersApplied="filterSettings"
            @filter="applyFilters"
            @resetFilters="initializeListing()"
          />
        </div>
      </div>
      <!--
        @name ModalElement
        @fire closeModal
      -->
      <ModalElement
        v-if="$root.showFilter"
        class="filterModal"
        @closeModal="cancel"
      >
        <!--
          @name ContributionListFilter
          @fire filter apply the filters
          @fire resetFilters
        -->
        <ContributionListFilter
          :key="filterHash"
          :filtersApplied="filterSettings"
          @filter="applyFilters"
          @resetFilters="initializeListing()"
        />
      </ModalElement>
    </div>
  </div>
</template>

<style>
    #app.mobile section.contributionlist h1 {
        padding-top: 35px;
    }

    section.contributionlist h1 {
        font-size: 2.25rem;
        font-weight: bold;
        color: #003063;
        word-break: break-word;
    }

    section.contributionlist div.teaserlist {
        margin-top: 20px;
    }

    section.contributionlist div.sidebar div.inner {
        position: sticky;
        top: 0;
    }

    section.contributionlist div.sidebar div.inner > button.dipasButton {
        height: 3.125rem;
        width: 14.375rem;
        padding-left: 21px;
        line-height: 1rem;
        margin-bottom: 40px;
    }

    section.contributionlist div.sidebar div.inner button.createContributionButton .customIcon {
        font-size: 1.5rem;
    }

    section.contributionlist p.noMoreEntries,
    section.contributionlist p.loadMore {
        text-align: center;
        cursor: pointer;
        margin-top: 40px;
    }

    section.contributionlist p.noMoreEntries {
        cursor: default;
    }

    section.contributionlist p.loadMore .material-icons {
        position: relative;
        top: 7px;
    }

    section.contributionlist div.spinner {
        margin-top: 50px;
    }

    section.contributionlist div.filterModal {
        top: 50px;
        margin: 0;
        padding: 0;
    }
</style>
