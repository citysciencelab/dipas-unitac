/**
 * @license GPL-2.0-or-later
 */

import _ from "underscore";

export const requestBroker = {
  methods: {
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

    doRequest: function (request) {
      if (!_.isUndefined(request.method) && !_.isUndefined(this.$http[request.method.toLowerCase()])) {
        let url = "drupal/dipas/" + request.endpoint;

        if (!_.isUndefined(request.params)) {
          const paramString = [];

          _.each(request.params, (value, key) => {
            paramString.push(key + "=" + value);
          });
          url += "?" + paramString.join("&");
        }
        return this.$http[request.method.toLowerCase()](url, request.payload ? request.payload : null);
      }
      console.error("Unknown request method!");
      return false;
    },

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
