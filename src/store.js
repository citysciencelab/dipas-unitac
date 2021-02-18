/**
 * @license GPL-2.0-or-later
 */

/* eslint-disable default-case */

import Vue from "vue";
import Vuex from "vuex";
import _ from "underscore";
import moment from "moment";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    basicApplicationSettings: {},
    schedule: {},
    contributionList: {},
    contributionData: {},
    conceptionData: {},
    contributionComments: {},
    relatedContributions: {},
    otherConceptions: {},
    statistics: {},
    referenceKeywordList: [],
    welcomemodal: {},
    keywords: {
      keywords: [],
      proposals: [],
      token: ""
    },
    theming: {
      leadingColor: "#003063",
      homeButtonLogo: "",
      menuLineLogo: ""
    }
  },
  actions: {
    readTheming: function (context, jsonFileName) {
      fetch(jsonFileName)
        .then(r => r.json())
        .then(json => {
          context.commit("theming", {
            menuColor: json.menuColor,
            homeButtonLogo: json.homeButtonLogo,
            menuLineLogo: json.menuLineLogo
          });
        });
    }
  },
  mutations: {
    invalidateStateCache: function (state, cacheID) {
      // cacheID has to be according to the following pattern:
      // [rating|comment]:[contribution|conception]:nodeID
      // For example: comment:contribution:12
      const cacheIdentifier = cacheID.split(":");

      // Comments are stored in the contributionComments index
      // in the state for both contributions and conceptions.
      if (cacheIdentifier[0] === "comment") {
        state.contributionComments[cacheIdentifier[2]].timestamp = 0;
      }

      switch (cacheIdentifier[1]) {

        case "contribution":
          // Invalidate every entry on the contributionList where the given
          // node is contained in.
          _.each(state.contributionList, (elem, index) => {
            if (_.pluck(elem.data.nodes, "nid").indexOf(cacheIdentifier[2]) !== -1) {
              state.contributionList[index].timestamp = 0;
            }
          });

          // When the rating was changed we need to invalidate the
          // according details object entry.
          if (cacheIdentifier[0] === "rating") {
            delete state.contributionData[cacheIdentifier[2]];
          }
          break;

        case "conception":
          // TODO - currently there is no rating on conceptions.
          //  Implement cache invalidation when there is one.
          break;

      }
    },
    addKeyword: function (state, payload) {
      state.keywords.keywords.push(payload);
    },
    removeKeyword: function (state, payload) {
      const index = state.keywords.keywords.indexOf(payload);

      if (index > -1) {
        state.keywords.keywords.splice(index, 1);
      }
    },
    referenceKeywordList: function (state, payload) {
      state.referenceKeywordList = payload;
    },
    removeProposal: function (state, payload) {
      const index = state.keywords.proposals.indexOf(payload);

      if (index > -1) {
        state.keywords.proposals.splice(index, 1);
      }
    },
    proposals: function (state, payload) {
      state.keywords.proposals = payload;
    },
    keywords: function (state, payload) {
      state.keywords.keywords = payload;
    },
    token: function (state, payload) {
      state.keywords.token = payload;
    },
    addProposal: function (state, payload) {
      state.keywords.proposals.push(payload);
    },
    initialization: function (state, payload) {
      state.basicApplicationSettings = _.extend({initialized: true}, payload);
    },
    endpoint: function (state, payload) {
      state[payload.endpoint] = {
        timestamp: new Date().getTime(),
        content: payload.content
      };
    },
    schedule: function (state, payload) {
      state.schedule = {
        timestamp: new Date().getTime(),
        data: payload
      };
    },
    statistics: function (state, payload) {
      state.statistics = {
        timestamp: new Date().getTime(),
        data: payload
      };
    },
    contributionList: function (state, payload) {
      const localOptions = _.extend({
          page: 1,
          filters: {},
          sort: {
            field: "created",
            direction: "DESC"
          }
        }, _.extend({}, _.pick(payload.options, (value) => !_.isUndefined(value)))),
        dataKey = localOptions.page + JSON.stringify(localOptions.filters) + JSON.stringify(localOptions.sort);

      state.contributionList[dataKey] = {
        timestamp: new Date().getTime(),
        data: _.clone(payload.data)
      };
    },
    contributionDetails: function (state, payload) {
      state.contributionData[payload.nid] = payload;
    },
    conceptionDetails: function (state, payload) {
      state.conceptionData[payload.nid] = payload;
    },
    relatedContributions: function (state, payload) {
      state.relatedContributions[payload.id] = {
        timestamp: new Date().getTime(),
        data: payload.data
      };
    },
    otherConceptions: function (state, payload) {
      state.otherConceptions[payload.id] = {
        timestamp: new Date().getTime(),
        data: payload.data
      };
    },
    contributionComments: function (state, payload) {
      state.contributionComments[payload.contributionID] = {
        timestamp: new Date().getTime(),
        data: payload.comments,
        commentcount: payload.commentcount
      };
    },
    theming: function (state, payload) {
      state.theming = {
        leadingColor: payload.menuColor ? payload.menuColor : "#003063",
        homeButtonLogo: payload.homeButtonLogo ? payload.homeButtonLogo : "",
        menuLineLogo: payload.menuLineLogo ? payload.menuLineLogo : ""
      };
    }
  },
  getters: {
    referenceKeywordList: function (state) {
      return state.referenceKeywordList;
    },
    keywords: function (state) {
      return state.keywords.keywords;
    },
    proposals: function (state) {
      return state.keywords.proposals;
    },
    token: function (state) {
      return state.keywords.token;
    },
    mainmenu: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.menus.main : [];
    },
    footermenu: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.menus.footer : [];
    },
    imagestyles: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.image_styles : [];
    },
    isKeywordServiceEnabled: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.keyword_service_enabled : false;
    },
    projectphase: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projectphase : "phase1";
    },
    frontpage: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.frontpage : "contributionmap";
    },
    projecttitle: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projecttitle : "";
    },
    projectperiod: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) && !_.isUndefined(state.basicApplicationSettings.projectperiod) ? state.basicApplicationSettings.projectperiod : {};
    },
    enabledPhase2: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) && !_.isUndefined(state.basicApplicationSettings.enabledPhase2) ? state.basicApplicationSettings.enabledPhase2 : false;
    },
    takesNewContributions: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.status === "open" : false;
    },
    projectRunning: function (state) {
      const now = moment().utc();

      return !_.isUndefined(state.basicApplicationSettings.initialized)
        ? now >= moment(state.basicApplicationSettings.projectperiod.start) && now <= moment(state.basicApplicationSettings.projectperiod.end)
        : false;
    },
    modalimage: function (state) {
      if (!_.isUndefined(state.basicApplicationSettings.initialized)) {
        if (state.basicApplicationSettings.modalimage.length) {
          return state.basicApplicationSettings.modalimage;
        }
        else if (state.basicApplicationSettings.modallogo.length) {
          return state.basicApplicationSettings.modallogo;
        }
      }
      return "";
    },
    footertext: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.footertext : "";
    },
    sidebar: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.sidebar : [];
    },
    endpoint: function (state) {
      return (endpoint) => _.isUndefined(state[endpoint])
        ? {}
        : state[endpoint];
    },
    schedule: function (state) {
      return state.schedule;
    },
    schedulemap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.schedule : "";
    },
    downloads: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.downloads : [];
    },
    statistics: function (state) {
      return state.statistics;
    },
    allCategories: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.taxonomy.categories : {};
    },
    allRubrics: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.taxonomy.rubrics : {};
    },
    useRubrics: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.taxonomy.rubrics_use : false;
    },
    contributionList: function (state) {
      return (options, lastPageLoaded, ttl) => {
        const localOptions = _.extend({
            filters: {},
            sort: {
              field: "created",
              direction: "DESC"
            }
          }, _.pick(options, (value) => !_.isUndefined(value))),
          contributionList = {
            pager: {
              currentPage: 1,
              totalPages: 1,
              itemsPerPage: 10,
              lastPage: true
            },
            totalNodes: 0,
            lastPageLoaded: 0
          },
          keyFilterPart = JSON.stringify(localOptions.filters) + JSON.stringify(localOptions.sort);

        let storedPageContent = "";

        if (lastPageLoaded > localOptions.page) {
          localOptions.page = lastPageLoaded;
        }

        storedPageContent = state.contributionList[localOptions.page + keyFilterPart];
        if (!_.isUndefined(storedPageContent) && ((storedPageContent.timestamp + ttl) > new Date().getTime())) {
          contributionList.nodes = _.clone(storedPageContent.data.nodes);
          contributionList.pager = _.clone(storedPageContent.data.pager);
          contributionList.totalNodes = storedPageContent.data.totalNodes;
          contributionList.lastPageLoaded = localOptions.page;

          if (contributionList.lastPageLoaded > 1) {
            for (let page = contributionList.lastPageLoaded - 1; page >= 1; page--) {
              const loadedPageData = state.contributionList[page + keyFilterPart];

              contributionList.nodes = _.clone(loadedPageData.data.nodes).concat(contributionList.nodes);
            }
          }
        }
        return contributionList;
      };
    },
    contributionDetails: function (state) {
      return (id) => state.contributionData[id];
    },
    conceptionDetails: function (state) {
      return (id) => state.conceptionData[id];
    },
    relatedContributions: function (state) {
      return (id) => state.relatedContributions[id];
    },
    otherConceptions: function (state) {
      return (id) => state.otherConceptions[id];
    },
    displayConceptionComments: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.display_existing_conception_comments : true;
    },
    displayContributionComments: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.comments.display : true;
    },
    conceptionCommentsState: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.conception_comments_state : "closed";
    },
    contributionCommentsState: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.comments.form : "closed";
    },
    ratingsAllowed: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.ratings : false;
    },
    commentMaxlength: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.comments.maxlength : 0;
    },
    contributionMaxlength: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.maxlength : 0;
    },
    contributionGeometryType: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.geometry : ["point"];
    },
    contributionComments: function (state) {
      return (id) => state.contributionComments[id];
    },
    categoryIcon: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id).field_category_icon;
    },
    categoryName: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id).name;
    },
    categoryColor: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id).field_color;
    },
    rubricName: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.rubrics.find(rub => rub.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.rubrics.find(rub => rub.id === id).name;
    },
    contributionmap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.contributionmap : "";
    },
    singlecontributionmap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.singlecontribution : {};
    },
    createcontributionmap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.createcontribution.url : "";
    },
    contributionsMustBeLocalized: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.createcontribution.must_be_localized : true;
    },
    projectlogo: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projectlogo : "";
    },
    partnerlogos: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.partnerlogos : "";
    },
    welcomemodal: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.welcomemodal : {};
    },
    projectowner: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projectowner : "";
    },
    surveyLink: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.menus.main.survey.url : "";
    },
    maintenanceMode: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.maintenanceMode : null;
    },
    maintenanceMessage: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.maintenanceMessage : "";
    },
    redirectURL: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.redirect_url : null;
    },
    leadingColor: function (state) {
      return state.theming.leadingColor;
    },
    homeButtonLogo: function (state) {
      return state.theming.homeButtonLogo;
    },
    menuLineLogo: function (state) {
      return state.theming.menuLineLogo;
    }
  },
  modules: {}
});


