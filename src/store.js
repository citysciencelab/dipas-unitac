/**
 * @license GPL-2.0-or-later
 */

/* eslint-disable default-case */

import Vue from "vue";
import Vuex from "vuex";
import _ from "underscore";
import moment from "moment";
const crypto = require("crypto");

Vue.use(Vuex);

export default new Vuex.Store({
  /**
   * The Vuex 'state' object
   * @name State vuex store state object
   * @type {Object}
   * @property {Object} basicApplicationSettings The basic application setting as object.
   * @property {Object} schedule The schedule data.
   * @property {Object} contributionList A object of all contributions.
   * @property {Object} contributionData The contribution data set.
   * @property {Object} conceptionData The conception data set.
   * @property {Object} contributionComments The contribution comments data set.
   * @property {Object} relatedContributions The data set of related contributions.
   * @property {Object} otherConceptions The data set of other/similar or corresponding conceptions.
   * @property {Object} statistics The statistics data set.
   * @property {Array} referenceKeywordList The list of referenced keywords.
   * @property {Object} welcomemodal The data set for the welcome modal.
   * @property {Object{{Array}keywords|{Array}proposals|{String}token}} keywords The keywords data set.
   * @property {Object{{String}leadingcolor|{String}homeButtonLogo|{String}menuLineLogo}} theming The theming data set.
   */
  state: {
    initializationTimestamp: null,
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
  /**
   * The "actions" object.
   * @name actions vuex store async actions object
   * @type {Object}
   */
  actions: {
    /**
     * Reads async the theming data from json and sets via mutation
     * @name readTheming
     * @param {Object} context The app context
     * @param {string} jsonFileName the name of the json file which holds the data
     * @returns {void}
     */
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
  /**
   * The "mutations" object.
   * @name Mutations vuex store mutations object
   * @type {Object}
   */
  mutations: {
    /**
     * Set the cache ID for heandling the cache updates
     * @name invalidateStateCache
     * @param {Object} state
     * @param {String} cacheID The cache ID
     * @returns {void}
     */
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
    /**
     * Adds a keyword to the keywords array
     * @name addKeyword
     * @param {Object} state
     * @param {String} payload The keyword to add
     * @returns {void}
     */
    addKeyword: function (state, payload) {
      state.keywords.keywords.push(payload);
    },
    /**
     * Removes a keyword from the keywords array
     * @name removeKeyword
     * @param {Object} state
     * @param {String} payload The keyword to remove
     * @returns {void}
     */
    removeKeyword: function (state, payload) {
      const index = state.keywords.keywords.indexOf(payload);

      if (index > -1) {
        state.keywords.keywords.splice(index, 1);
      }
    },
    /**
     * List of referenced keywords
     * @name referenceKeywordList
     * @param {Object} state
     * @param {Array} payload
     * @returns {void}
     */
    referenceKeywordList: function (state, payload) {
      state.referenceKeywordList = payload;
    },
    /**
     * Removes a single roposal from list
     * @name removeProposal
     * @param {Object} state
     * @param {String} payload proposal to remove
     * @returns {void}
     */
    removeProposal: function (state, payload) {
      const index = state.keywords.proposals.indexOf(payload);

      if (index > -1) {
        state.keywords.proposals.splice(index, 1);
      }
    },
    /**
     * Sets a list of proposals
     * @name removeProposal
     * @param {Object} state
     * @param {Array} payload proposals
     * @returns {void}
     */
    proposals: function (state, payload) {
      state.keywords.proposals = payload;
    },
    /**
     * Sets a list of keywords
     * @name removeProposal
     * @param {Object} state
     * @param {Array} payload keywords
     * @returns {void}
     */
    keywords: function (state, payload) {
      state.keywords.keywords = payload;
    },
    /**
     * Sets a token to check changes in text of first step form
     * @name token
     * @param {Object} state
     * @param {String} payload token
     * @returns {void}
     */
    token: function (state, payload) {
      state.keywords.token = payload;
    },
    /**
     * Sets a list of keywords
     * @name addProposal
     * @param {Object} state
     * @param {String} payload keywords
     * @returns {void}
     */
    addProposal: function (state, payload) {
      state.keywords.proposals.push(payload);
    },
    /**
     * Sets the basic application settings and extends with a bool if the initialisation is already done.
     * @name initialization
     * @param {Object} state
     * @param {Object} payload basicApplicationSettings
     * @returns {void}
     */
    initialization: function (state, payload) {
      state.basicApplicationSettings = _.extend({initialized: true}, payload);
      state.initializationTimestamp = moment();
    },
    /**
     * Sets the endpoint to use from the frontend
     * @name endpoint
     * @param {Object} state
     * @param {Object} payload endpoint
     * @returns {void}
     */
    endpoint: function (state, payload) {
      state[payload.endpoint] = {
        timestamp: new Date().getTime(),
        content: payload.content
      };
    },
    /**
     * Sets the schedule data with timestamp and dataset
     * @name schedule
     * @param {Object} state
     * @param {Object} payload schedule
     * @returns {void}
     */
    schedule: function (state, payload) {
      state.schedule = {
        timestamp: new Date().getTime(),
        data: payload
      };
    },
    /**
     * Sets the statistics dataset with timestamp
     * @name statistics
     * @param {Object} state
     * @param {Object} payload statistics
     * @returns {void}
     */
    statistics: function (state, payload) {
      state.statistics = {
        timestamp: new Date().getTime(),
        data: payload
      };
    },
    /**
     * Sets a list of all contributions with sort criteria
     * @name contributionList
     * @param {Object} state
     * @param {Object} payload contributionList
     * @returns {void}
     */
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
    /**
     * Sets the Details for a given contribution
     * @name contributionDetails
     * @param {Object} state
     * @param {Object} payload contributionDetails
     * @returns {void}
     */
    contributionDetails: function (state, payload) {
      state.contributionData[payload.nid] = payload;
    },
    /**
     * Sets the Details for a given conception
     * @name conceptionDetails
     * @param {Object} state
     * @param {Object} payload contributionData
     * @returns {void}
     */
    conceptionDetails: function (state, payload) {
      state.conceptionData[payload.nid] = payload;
    },
    /**
     * Sets a data set with related contributions
     * @name relatedContributions
     * @param {Object} state
     * @param {Object} payload relatedContributions
     * @returns {void}
     */
    relatedContributions: function (state, payload) {
      state.relatedContributions[payload.id] = {
        timestamp: new Date().getTime(),
        data: payload.data
      };
    },
    /**
     * Sets a data set with other corresponding contributions
     * @name otherConceptions
     * @param {Object} state
     * @param {Object} payload otherConceptions
     * @returns {void}
     */
    otherConceptions: function (state, payload) {
      state.otherConceptions[payload.id] = {
        timestamp: new Date().getTime(),
        data: payload.data
      };
    },
    /**
     * Sets a comments data set for a given contribution(id)
     * @name contributionComments
     * @param {Object} state
     * @param {Object} payload contributionComments
     * @returns {void}
     */
    contributionComments: function (state, payload) {
      state.contributionComments[payload.contributionID] = {
        timestamp: new Date().getTime(),
        data: payload.comments,
        commentcount: payload.commentcount
      };
    },
    /**
     * Sets a theming data set with a heading color, logo and menu line logo
     * @name theming
     * @param {Object} state
     * @param {Object} payload theming
     * @returns {void}
     */
    theming: function (state, payload) {
      state.theming = {
        leadingColor: payload.menuColor ? payload.menuColor : "#003063",
        homeButtonLogo: payload.homeButtonLogo ? payload.homeButtonLogo : "",
        menuLineLogo: payload.menuLineLogo ? payload.menuLineLogo : ""
      };
    }
  },
  /**
   * The store 'getters' object.
   * @name Getters vuex store getters object
   * @type {Object}
   */
  getters: {
    /**
     * Serves a array list of referenced keywords
     * @name referenceKeywordList
     * @param {Object} state .referenceKeywordList
     * @returns {Array{String}} referenceKeywordList
     */
    referenceKeywordList: function (state) {
      return state.referenceKeywordList;
    },
    /**
     * Serves a array list of keywords
     * @name keywords
     * @param {Object} state .keywords.keywords
     * @returns {Array{String}} keywords
     */
    keywords: function (state) {
      return state.keywords.keywords;
    },
    /**
     * Serves a array list of proposals
     * @name proposals
     * @param {Object} state .keywords.proposals
     * @returns {Array{String}} proposals
     */
    proposals: function (state) {
      return state.keywords.proposals;
    },
    /**
     * Serves a token string
     * @name token
     * @param {Object} state
     * @returns {String} token
     */
    token: function (state) {
      return state.keywords.token;
    },
    /**
     * Serves a array list of main menu item objects
     * @name mainmenu
     * @param {Object} state
     * @returns {Array{Object}} mainmenu
     */
    mainmenu: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.menus.main : [];
    },
    /**
     * Serves a array list of footer menu item objects
     * @name footermenu
     * @param {Object} state
     * @returns {Array{Object}} footermenu
     */
    footermenu: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.menus.footer : [];
    },
    /**
     * Serves a array list of footer menu item objects
     * @name imagestyles
     * @param {Object} state
     * @returns {Array} imagestyles
     */
    imagestyles: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.image_styles : [];
    },
    /**
     * Serves a bool wether the keyword service is enabled or not.
     * @name isKeywordServiceEnabled
     * @param {Object} state
     * @returns {Bool} isKeywordServiceEnabled
     */
    isKeywordServiceEnabled: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.keyword_service_enabled : false;
    },
    /**
     * Serves a string of the actual project phase.
     * @name projectphase
     * @param {Object} state
     * @returns {String} projectphase
     */
    projectphase: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projectphase : "phase1";
    },
    /**
     * Serves a string of the actual setted frontpage design.
     * @name frontpage
     * @param {Object} state
     * @returns {String} frontpage
     */
    frontpage: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.frontpage : "contributionmap";
    },
    /**
     * Serves the title of the project/contribution.
     * @name projecttitle
     * @param {Object} state
     * @returns {String} projecttitle
     */
    projecttitle: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projecttitle : "";
    },
    /**
     * Serves the actual period object of the project.
     * @name projecttitle
     * @param {Object} state
     * @returns {Object} projectperiod
     */
    projectperiod: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) && !_.isUndefined(state.basicApplicationSettings.projectperiod) ? state.basicApplicationSettings.projectperiod : {};
    },
    /**
     * Serves a boolean wether the 2nd phase of the project is still running or not.
     * @name enabledPhase2
     * @param {Object} state
     * @returns {Object} enabledPhase2
     */
    enabledPhase2: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) && !_.isUndefined(state.basicApplicationSettings.enabledPhase2) ? state.basicApplicationSettings.enabledPhase2 : false;
    },
    /**
     * Serves a boolean wether new contributions will accepted or not.
     * @name takesNewContributions
     * @param {Object} state
     * @returns {Boolean} takesNewContributions
     */
    takesNewContributions: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.status === "open" : false;
    },
    /**
     * Serves a boolean wether the project is active/running or not.
     * @name projectRunning
     * @param {Object} state
     * @returns {Boolean} projectRunning
     */
    projectRunning: function (state) {
      const now = moment().utc();

      return !_.isUndefined(state.basicApplicationSettings.initialized)
        ? now >= moment(state.basicApplicationSettings.projectperiod.start) && now <= moment(state.basicApplicationSettings.projectperiod.end)
        : false;
    },
    /**
     * Serves a string with the image or logo for the frontpage modal.
     * @name modalimage
     * @param {Object} state
     * @returns {String} modalimage
     */
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
    /**
     * Serves a string of the footertext.
     * @name footertext
     * @param {Object} state
     * @returns {String} footertext
     */
    footertext: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.footertext : "";
    },
    /**
     * Serves a array list of sidebar items.
     * @name sidebar
     * @param {Object} state
     * @returns {Array} sidebar items
     */
    sidebar: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.sidebar : [];
    },
    /**
     * Serves the endpoint object.
     * @name endpoint
     * @param {Object} state
     * @returns {Object} endpoint
     */
    endpoint: function (state) {
      return (endpoint) => _.isUndefined(state[endpoint])
        ? {}
        : state[endpoint];
    },
    /**
     * Serves the schedule object.
     * @name schedule
     * @param {Object} state
     * @returns {Object} schedule
     */
    schedule: function (state) {
      return state.schedule;
    },
    /**
     * Serves the schedule map as url query string.
     * @name schedule
     * @param {Object} state
     * @returns {Object} schedule
     */
    schedulemap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.schedule : "";
    },
    /**
     * Serves a array list of item for the download box.
     * @name downloads
     * @param {Object} state
     * @returns {Array} downloads
     */
    downloads: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.downloads : [];
    },
    /**
     * Serves a object of statistics data.
     * @name statistics
     * @param {Object} state
     * @returns {Object} statistics
     */
    statistics: function (state) {
      return state.statistics;
    },
    /**
     * Serves a object of all categories.
     * @name allCategories
     * @param {Object} state
     * @returns {Object} allCategories
     */
    allCategories: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.taxonomy.categories : {};
    },
    /**
     * Serves a object of all rubrics.
     * @name allRubrics
     * @param {Object} state
     * @returns {Object} allRubrics
     */
    allRubrics: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.taxonomy.rubrics : {};
    },
    /**
     * Serves a bool wether rubrics are in use or not.
     * @name useRubrics
     * @param {Object} state
     * @returns {Boolean} useRubrics
     */
    useRubrics: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.taxonomy.rubrics_use : false;
    },
    /**
     * Serves a function with the parameters: options, lastPageloaded, ttl to build a contributions list.
     * @name contributionList
     * @param {Object} state
     * @param {Object} options the options object
     * @param {String} lastPageLoaded the last loaded page string
     * @param {Number} ttl time to live in milliseconds
     * @returns {Function({Object}options|{String}lastPageLoaded|{Number}ttl)} the returned function
     */
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
    /**
     * Serves a function to create the contribution detail data object with the parameters: id
     * @name contributionDetails
     * @param {Object} state
     * @param {String} id the id of the leading contribution
     * @returns {Function({String}id)} the returned function
     */
    contributionDetails: function (state) {
      return (id) => state.contributionData[id];
    },
    /**
     * Serves a function to create the conception detail data object with the parameters: id
     * @name conceptionDetails
     * @param {Object} state
     * @param {String} id the id of the leading conception
     * @returns {Function({String}id)} the returned function
     */
    conceptionDetails: function (state) {
      return (id) => state.conceptionData[id];
    },
    /**
     * Serves a function to create the related contribution data object with the parameters: id
     * @name conceptionDetails
     * @param {Object} state
     * @param {String} id the id of the leading contribution
     * @returns {Function({String}id)} the returned function
     */
    relatedContributions: function (state) {
      return (id) => state.relatedContributions[id];
    },
    /**
     * Serves a function to create the conception detail data object with the parameters: id
     * @name otherConceptions
     * @param {Object} state
     * @param {String} id the id of the leading conception
     * @returns {Function({String}id)} the returned function
     */
    otherConceptions: function (state) {
      return (id) => state.otherConceptions[id];
    },
    /**
     * Serves a bool wether the conception comments will be displayed or not.
     * @name displayConceptionComments
     * @param {Object} state
     * @returns {Boolean}
     */
    displayConceptionComments: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.display_existing_conception_comments : true;
    },
    /**
     * Serves a bool wether the contribution comments will be displayed or not.
     * @name displayContributionComments
     * @param {Object} state
     * @returns {Boolean}
     */
    displayContributionComments: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.comments.display : true;
    },
    /**
     * Serves a string with the actual conception comments state.
     * @name conceptionCommentsState
     * @param {Object} state
     * @returns {String} conception comments state
     */
    conceptionCommentsState: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.conception_comments_state : "closed";
    },
    /**
     * Serves a string with the actual conception comments state.
     * @name contributionCommentsState
     * @param {Object} state
     * @returns {String} contribution comments state
     */
    contributionCommentsState: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.comments.form : "closed";
    },
    /**
     * Serves a bool wether ratings allowed or not.
     * @name ratingsAllowed
     * @param {Object} state
     * @returns {Boolean} ratings allowed
     */
    ratingsAllowed: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.ratings : false;
    },
    displayRatings: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized)
        ? state.basicApplicationSettings.contributions.display_existing_ratings
        : false;
    },
    /**
     * Serves a number with the maximum length of a comment.
     * @name commentMaxlength
     * @param {Object} state
     * @returns {Number} max length of the comment
     */
    commentMaxlength: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.comments.maxlength : 0;
    },
    /**
     * Serves a number with the maximum length of a contribution.
     * @name contributionMaxlength
     * @param {Object} state
     * @returns {Number} max length of the contribution
     */
    contributionMaxlength: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.maxlength : 0;
    },
    /**
     * Serves  a string inside an array with the value point, line or polygon.
     * @name contributionGeometryType
     * @param {Object} state
     * @returns {Array{String}} contribution geometry type
     */
    contributionGeometryType: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.contributions.geometry : ["point"];
    },
    /**
     * Serves a function to create the contribution comments data object with the parameters: id
     * @name contributionComments
     * @param {Object} state
     * @param {String} id the id of the leading contribution
     * @returns {Function({String}id)} the returned function
     */
    contributionComments: function (state) {
      return (id) => state.contributionComments[id];
    },
    /**
     * Serves a function to create the mdi-icon string with the parameters: id
     * @see {@link http://mdi-icons.org|Google Material Icons} for further material icon informations
     * @name categoryIcon
     * @param {Object} state
     * @param {String} id the id of the leading contribution
     * @returns {Function({String}id)} the returned function
     */
    categoryIcon: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id).field_category_icon;
    },
    /**
     * Serves a function to create the category name string with the parameters: id
     * @name categoryName
     * @param {Object} state
     * @param {String} id the id of the leading contribution
     * @returns {Function({String}id)} the returned function
     */
    categoryName: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id).name;
    },
    /**
     * Serves a function to create the category color string with the parameters: id
     * @name categoryColor
     * @param {Object} state
     * @param {String} id the id of the leading contribution
     * @returns {Function({String}id)} the returned function
     */
    categoryColor: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.categories.find(cat => cat.id === id).field_color;
    },
    /**
     * Serves a function to create the rubric name string with the parameters: id
     * @name rubricName
     * @param {Object} state
     * @param {String} id the id of the leading contribution
     * @returns {Function({String}id)} the returned function
     */
    rubricName: function (state) {
      return (id) => _.isUndefined(state.basicApplicationSettings.initialized) || _.isUndefined(state.basicApplicationSettings.taxonomy.rubrics.find(rub => rub.id === id))
        ? ""
        : state.basicApplicationSettings.taxonomy.rubrics.find(rub => rub.id === id).name;
    },
    /**
     * Serves a string with contribution map design.
     * @name contributionmap
     * @param {Object} state
     * @returns {String} contribution map url query
     */
    contributionmap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.contributionmap : "";
    },
    /**
     * Serves  a object of single contribution map data.
     * @name singlecontributionmap
     * @param {Object} state
     * @returns {Object} single contribution map data
     */
    singlecontributionmap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.singlecontribution : {};
    },
    /**
     * Serves a string of an url that creates a masterportal contribution map.
     * @name createcontributionmap
     * @param {Object} state
     * @returns {String} contribution map creation url query
     */
    createcontributionmap: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.createcontribution.url : "";
    },
    /**
     * Serves a bool wether a contribution must be localized or not.
     * @name contributionsMustBeLocalized
     * @param {Object} state
     * @returns {Boolean} contribution must be localized
     */
    contributionsMustBeLocalized: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.masterportal_instances.createcontribution.must_be_localized : true;
    },
    /**
     * Serves a string of the project logo src.
     * @name projectlogo
     * @param {Object} state
     * @returns {String} project logo src
     */
    projectlogo: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projectlogo : "";
    },
    /**
     * Serves a list of partner logo src's or an empty string.
     * @name partnerlogos
     * @param {Object} state
     * @returns {String|Array} partner logos src
     */
    partnerlogos: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.partnerlogos : "";
    },
    /**
     * Serves a object of welcome modal data set.
     * @name welcomemodal
     * @param {Object} state
     * @returns {Object} welcomemodal
     */
    welcomemodal: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.welcomemodal : {};
    },
    /**
     * Serves a string of the project owners name
     * @name projectowner
     * @param {Object} state
     * @returns {String} project owner
     */
    projectowner: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.projectowner : "";
    },
    /**
     * Serves a string with the external link to a survey service. eg. limesurvey
     * @name surveyLink
     * @param {Object} state
     * @returns {String} survey link
     */
    surveyLink: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.menus.main.survey.url : "";
    },
    /**
     * Serves null if the maintenance mode is off.
     * @name maintenanceMode
     * @param {Object} state
     * @returns {Boolean|Null} maintenance mode
     */
    maintenanceMode: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.maintenanceMode : null;
    },
    /**
     * Serves a maintenance message string
     * @name maintenanceMode
     * @param {Object} state
     * @returns {String} maintenace message string
     */
    maintenanceMessage: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.maintenanceMessage : "";
    },
    /**
     * Serves a string with the url to redirect.
     * @name redirectURL
     * @param {Object} state
     * @returns {String|Null} redirect url
     */
    redirectURL: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.redirect_url : null;
    },
    /**
     * Serves a string with the leading hex color of the project.
     * @name leadingColor
     * @param {Object} state
     * @returns {String} leading hex logocolor
     */
    leadingColor: function (state) {
      return state.theming.leadingColor;
    },
    /**
     * Serves a string with the home button logo src.
     * @name homeButtonLogo
     * @param {Object} state
     * @returns {String} home button logo src
     */
    homeButtonLogo: function (state) {
      return state.theming.homeButtonLogo;
    },
    /**
     * Serves a string with the menu line logo src.
     * @name menuLineLogo
     * @param {Object} state
     * @returns {String} menu line logo src
     */
    menuLineLogo: function (state) {
      return state.theming.menuLineLogo;
    },
    concetionButtonName: function (state) {
      return !_.isUndefined(state.basicApplicationSettings.initialized) ? state.basicApplicationSettings.menus.main.conceptionlist.name : null;
    },
    decryptedToken: function (state) {
      return (passphrase) => {
        try {
          const decipher = crypto.createDecipheriv("aes-256-cbc", passphrase, state.basicApplicationSettings.signature),
            decrypted = decipher.update(state.basicApplicationSettings.checksum, "base64", "utf8");

          return decrypted + decipher.final("utf8");
        }
        catch (e) {
          return false;
        }
      };
    },
    requestToken: function (state, getters) {
      return (passphrase) => {
        const securityToken = getters.decryptedToken(passphrase);

        if (securityToken) {
          const timestamp = Number(state.basicApplicationSettings.timestamp),
            timediff = moment().diff(state.initializationTimestamp, "seconds"),
            requesttime = timestamp + timediff,
            requestToken = securityToken + ":|:" + requesttime,
            cipher = crypto.createCipheriv("aes-256-cbc", passphrase, state.basicApplicationSettings.signature);
          let encrypted = cipher.update(requestToken, "utf8", "base64");

          encrypted += cipher.final("base64");

          return encrypted.replace(/\+/g, "%2b");
        }

        return false;
      };
    }
  },
  modules: {}
});


