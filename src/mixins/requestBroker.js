/**
 * @license GPL-2.0-or-later
 */

import _ from "underscore";

export const requestBroker = {
  methods: {
    /**
     * Load initial dipas settings for the app and contribution into the vuex store
     * @returns {Function()} the returned function for doing the initial request on API and writes the response data in $tore
     */
    initialize: function () {
      return this.doRequest({
        method: "GET",
        endpoint: "init"
      }).then(response => {
        this.$store.commit("initialization", response.body);
      }, response => {
        this.handleError(response);
      });
    },
    /**
     * Loads REST endpoints and commit it into the vuex store
     * @param {String} endpoint eg. "contributions" to load contributions
     * @returns {void}
     */
    loadEndpoint: function (endpoint) {
      const ttl = 60 * 60 * 1000,
        data = this.$store.getters.endpoint(endpoint);

      if (_.isUndefined(data.content) || ((data.timestamp + ttl) < new Date().getTime())) {
        this.doRequest({
          method: "GET",
          endpoint: endpoint
        }).then(response => {
          this.$store.commit("endpoint", {endpoint, content: response.body});
          this.pageContent = response.body;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.pageContent = data.content;
      }
    },
    /**
     * Load schedule data and commit it into the vuex store
     * @returns {void}
     */
    loadSchedule: function () {
      const ttl = 5 * 60 * 1000,
        list = this.$store.getters.schedule;

      if (_.isUndefined(list.data) || ((list.timestamp + ttl) < new Date().getTime())) {
        this.doRequest({
          method: "GET",
          endpoint: "schedule"
        }).then(response => {
          this.$store.commit("schedule", response.body);
          this.schedule = response.body;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.schedule = list.data;
      }
    },
    /**
     * Load contribution area (extent) data
     * @returns {void}
     */
    loadContributionsExtend: function () {
      this.doRequest({
        method: "GET",
        endpoint: "contributionmap"
      }).then(response => {
        this.extent = response.body.extent;
      }, response => {
        this.handleError(response);
      });
    },
    /**
     * Load contribution data, creates a list and commit it to the vuex store
     * @param {Object} options options Object
     * @returns {void}
     */
    loadContributionList: function (options = {}) {
      // Time to live for an already loaded list (in milliseconds)
      const ttl = 2 * 60 * 1000,
        localOptions = _.extend({page: 1, filters: {}, sort: {field: "created", direction: "DESC"}}, _.extend({}, _.pick(options, (value) => !_.isUndefined(value)))),
        loadedKeyOptionsPart = JSON.stringify(localOptions.filters) + JSON.stringify(localOptions.sort),
        lastPageLoadedKey = "contributionListLastPageLoaded" + loadedKeyOptionsPart,
        lastPageLoaded = !_.isUndefined(this.$root[lastPageLoadedKey]) ? this.$root[lastPageLoadedKey] : 0,
        list = this.$store.getters.contributionList(localOptions, lastPageLoaded, ttl);

      if (list.lastPageLoaded < localOptions.page) {
        const params = {
          page: localOptions.page,
          sort: localOptions.sort.field,
          direction: localOptions.sort.direction
        };

        if (!_.isUndefined(localOptions.itemsPerPage)) {
          params.itemsPerPage = localOptions.itemsPerPage;
        }

        _.each(localOptions.filters, (value, filter) => {
          if (value.length) {
            params[filter] = value.join(",");
          }
        });

        this.doRequest({
          method: "GET",
          endpoint: "contributionlist",
          params
        }).then(response => {
          this.$store.commit("contributionList", {data: response.body, options: localOptions});
          this.$root[lastPageLoadedKey] = response.body.pager.currentPage;
          this.contributionList = this.$store.getters.contributionList(localOptions, this.$root[lastPageLoadedKey], ttl);
          this.loading = false;
          this.showNodes = true;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.contributionList = list;
        this.loading = false;
        this.showNodes = true;
      }
    },
    /**
     * Load the detailed data for a single contribution (id) and commit it to the vuex store
     * @param {String} id Contribution ID
     * @returns {void}
     */
    loadContribution: function (id) {
      if (_.isUndefined(this.$store.getters.contributionDetails(id))) {
        this.doRequest({
          method: "GET",
          endpoint: "contributiondetails/" + id
        }).then(response => {
          this.$store.commit("contributionDetails", response.body);
          this.contribution = response.body;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.contribution = this.$store.getters.contributionDetails(id);
      }
    },
    /**
     * Load conception data and commit it to the vuex store
     * @param {String} id Conception ID
     * @returns {void}
     */
    loadConception: function (id) {
      if (_.isUndefined(this.$store.getters.conceptionDetails(id))) {
        this.doRequest({
          method: "GET",
          endpoint: "conceptiondetails/" + id
        }).then(response => {
          this.$store.commit("conceptionDetails", response.body);
          this.pageContent = response.body;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.pageContent = this.$store.getters.conceptionDetails(id);
      }
    },
    /**
     * Load related contributions data and commit it to the vuex store
     * @param {String} id Contribution ID
     * @returns {void}
     */
    loadRelatedContributions: function (id) {
      const ttl = 5 * 60 * 1000,
        cache = this.$store.getters.relatedContributions(id);

      if (_.isUndefined(cache) || (cache.timestamp + ttl) < new Date().getTime()) {
        this.doRequest({
          method: "GET",
          endpoint: "relatedcontributions/" + id
        }).then(response => {
          this.$store.commit("relatedContributions", {id, data: response.body});
          this.relatedContributions = response.body.related;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.relatedContributions = cache.data.related;
      }
    },
    /**
     * Load some other similar and corresponding conceptions data
     * to show it in the right sidebar
     * @param {String} id conception ID
     * @returns {void}
     */
    loadOtherConceptions: function (id) {
      const ttl = 60 * 60 * 1000,
        cache = this.$store.getters.otherConceptions(id);

      if (_.isUndefined(cache) || (cache.timestamp + ttl) < new Date().getTime()) {
        this.doRequest({
          method: "GET",
          endpoint: "otherconceptions/" + id
        }).then(response => {
          this.$store.commit("otherConceptions", {id, data: response.body});
          this.otherConceptions = response.body.otherConceptions;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.otherConceptions = cache.data.otherConceptions;
      }
    },
    /**
     * Saves a contribution dataset in drupal backend and redirect to the contribution detail page
     * @param {Object} data contribution dataset
     * @returns {void}
     */
    saveContribution: function (data) {
      this.doRequest({
        method: "POST",
        endpoint: "addcontribution",
        payload: data
      }).then(response => {
        this.$root.showCreateContributionModal = false;
        this.$router.push("/contribution/" + response.body.nid);
      }, response => {
        this.handleError(response);
      });
    },
    /**
     * Saves a rating dataset in drupal backend
     * @param {Object} data rating dataset
     * @returns {void}
     */
    addRating: function (data) {
      this.doRequest({
        method: "POST",
        endpoint: "rate/" + data.id,
        payload: data
      }).then(response => {
        this.upVotes = response.body.results.upVotes;
        this.downVotes = response.body.results.downVotes;
        this.cookieData = this.$cookies.get("dipas");
        this.savingInProgress = false;
      }, response => {
        this.handleError(response);
        this.savingInProgress = false;
      });
    },
    /**
     * Loads all comments to a given contribution id and store it in vuex store
     * @param {String} contributionID Contibution ID
     * @returns {void}
     */
    loadComments: function (contributionID) {
      const ttl = 1 * 60 * 1000,
        comments = this.$store.getters.contributionComments(contributionID);

      if (_.isUndefined(comments) || (comments.timestamp + ttl) < new Date().getTime()) {
        this.doRequest({
          method: "GET",
          endpoint: "getcomments/" + contributionID
        }).then(response => {
          this.$store.commit("contributionComments", response.body);
          this.commentcount = response.body.commentcount;
          this.comments = response.body.comments;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.commentcount = comments.commentcount;
        this.comments = comments.data;
      }
    },
    /**
     * Adds and saves a comments to the contribution, sets it to vuex store and reload the comments
     * @param {Object} data comment dataset
     * @returns {void}
     */
    addComment: function (data) {
      this.doRequest({
        method: "POST",
        endpoint: "addcomment",
        payload: data
      }).then(response => {
        this.$store.commit("contributionComments", response.body);
        this.commentcount = response.body.commentcount;
        this.comments = response.body.comments;
        this.comment = "";
        this.saving = false;
        this.$emit("reloadComments", response.body.insertedCommentID);
        this.$root.$emit("resetCommentForms");
      }, response => {
        this.handleError(response);
      });
    },
    /**
     * Request Keywords from natural language processing API and serves it for 2nd step of the contribution wizard
     * @param {String} data the whole textarea (step 1) content written by client
     * @returns {void}
     */
    requestKeywords: function (data) {
      this.doRequest({
        method: "POST",
        endpoint: "keywords",
        payload: data
      }).then(response => {
        this.stepvalue = _.extend(
          this.contributionData.step2,
          {
            proposals: response.body.keywords,
            token: response.body.token
          }
        );

        this.contributionData.step2 = _.extend(
          this.contributionData.step2,
          {
            proposals: response.body.keywords,
            token: response.body.token
          }
        );
      }, response => {
        this.handleError(response);
      });
    },
    /**
     * Loads the statistical data and commit it to the vuex store
     * @returns {void}
     */
    loadStatisticalData: function () {
      // Time to live for an already loaded list (in milliseconds)
      const ttl = 10 * 60 * 1000,
        list = this.$store.getters.statistics;

      if (_.isUndefined(list.data) || ((list.timestamp + ttl) < new Date().getTime())) {
        this.doRequest({
          method: "GET",
          endpoint: "statistics"
        }).then(response => {
          this.$store.commit("statistics", response.body);
          this.statistics = response.body;
        }, response => {
          this.handleError(response);
        });
      }
      else {
        this.statistics = list.data;
      }
    },
    /**
     * Saves the confirmed cockies true
     * @returns {void}
     */
    confirmCookies: function () {
      this.doRequest({
        method: "POST",
        endpoint: "confirmcookies",
        payload: {confirmCookies: true}
      }).then(() => {
        // Intentionally blank - nothing left to do here.
      }, response => {
        this.handleError(response);
      });
    },
    /**
     * Just do the single request to the backend
     * @param {Object{method: {GET|POST} | endpoint: {String}}} request the request object
     * @returns {Object|Boolean} respose dataset or return false if error
     */
    doRequest: function (request) {
      if (!_.isUndefined(request.method) && !_.isUndefined(this.$http[request.method.toLowerCase()])) {
        const paramString = [],
          requestToken = this.$store.getters.requestToken("NYZM6QccC8f7pZEoN7U37TxyExfPBxIP");

        let url = "drupal/dipas/" + request.endpoint;

        if (requestToken) {
          paramString.push("token=" + requestToken);
        }

        if (!_.isUndefined(request.params)) {
          _.each(request.params, (value, key) => {
            paramString.push(key + "=" + value);
          });
        }

        url += "?" + paramString.join("&");

        return this.$http[request.method.toLowerCase()](url, request.payload ? request.payload : null);
      }
      console.error("Unknown request method!");
      return false;
    },
    /**
     * Handles the error cases and shows the corresponding modal
     * @param {Object} response the request object
     * @param {String} customErrorMessage the custom error message
     * @returns {void}
     */
    handleError: function (response, customErrorMessage) {
      let errorMessage;

      switch (response.status) {
        case 400:
          errorMessage = this.$t("ErrorMessages.400");
          break;
        case 403:
          errorMessage = this.$t("ErrorMessages.403");
          break;
        case 404:
          errorMessage = this.$t("ErrorMessages.404");
          break;
        case 409:
          errorMessage = this.$t("ErrorMessages.409");
          break;
        default:
          errorMessage = `HTTP/${response.status} - ${response.statusText}`;
      }

      if (!_.isUndefined(customErrorMessage)) {
        errorMessage = `${customErrorMessage}<br/>(${errorMessage})`;
      }
      this.$root.showErrorModal(this.$t("ErrorMessages.unknown"), errorMessage);
    }
  }
};
