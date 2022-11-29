/**
 * @license GPL-2.0-or-later
 */
// Core components
import Vue from "vue";
import VueRouter from "vue-router";
import VueResource from "vue-resource";
import VueCookies from "vue-cookies";
import VueScrollTo from "vue-scrollto";
import VuePageTitle from "vue-page-title";
import ModalElement from "./basicComponents/ModalElement.vue";
import App from "./App.vue";
import store from "./store.js";
import breakpoints from "@eli5/bootstrap-breakpoints-vue";
import i18next from "i18next";
import VueI18Next from "@panter/vue-i18next";


// Bootstrap & CSS
import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import "material-design-icons/iconfont/material-icons.css";
import "./assets/styles.css";

// Pages
import Frontpage from "./pages/Frontpage/Frontpage.vue";
import ContributionMap from "./pages/ContributionMap/ContributionMap.vue";
import ContributionList from "./pages/ContributionList/ContributionList.vue";
import ContributionDetails from "./pages/ContributionDetails/ContributionDetails.vue";
import ConceptionList from "./pages/ConceptionList/ConceptionList.vue";
import ConceptionDetails from "./pages/ConceptionDetails/ConceptionDetails.vue";
import ProjectInfo from "./pages/ProjectInfo/ProjectInfo.vue";
import SchedulePage from "./pages/Schedule/SchedulePage.vue";
import StatisticsPage from "./pages/Statistics/StatisticsPage.vue";
import SurveyPage from "./pages/Survey/SurveyPage.vue";
import CustomPage from "./pages/CustomPage/CustomPage.vue";
import DataPrivacy from "./pages/DataPrivacy/DataPrivacy.vue";
import ImprintPage from "./pages/Imprint/ImprintPage.vue";
import FAQPage from "./pages/FAQ/FAQPage.vue";
import ContactPage from "./pages/Contact/ContactPage.vue";

Vue.use(VueRouter);
Vue.use(VueResource);
Vue.use(VueCookies);
Vue.use(VueScrollTo);
Vue.use(breakpoints);
Vue.use(VuePageTitle);
Vue.use(VueI18Next);
Vue.component("ModalElement", ModalElement);

// Language files
import translate from "./lang/index.js";

Vue.config.productionTip = false;

const i18n = new VueI18Next(i18next),
  configElement = document.getElementById("config"),
  router = new VueRouter({
    routes: [
      {
        path: "/",
        alias: "/frontpage",
        component: Frontpage,
        props: true,
        meta: {
          pageClass: "frontpage",
          hasCreateButton: true
        },
        children: []
      },
      {
        path: "/contributionmap",
        component: ContributionMap,
        props: true,
        meta: {
          pageClass: "contributionmap",
          hasCreateButton: true
        },
        children: []
      },
      {
        path: "/contributionlist",
        component: ContributionList,
        props: true,
        meta: {
          pageClass: "contributionlist",
          hasCreateButton: true
        },
        children: []
      },
      {
        path: "/contribution/new",
        component: ContributionMap,
        props: true,
        meta: {
          pageClass: "contributionmap",
          hasCreateButton: true,
          openContributionModal: true
        },
        children: []
      },
      {
        path: "/contribution/:id",
        component: ContributionDetails,
        props: true,
        meta: {
          pageClass: "contributiondetails",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/conceptionlist",
        component: ConceptionList,
        props: true,
        meta: {
          pageClass: "conceptionlist",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/conception/:id",
        component: ConceptionDetails,
        props: true,
        meta: {
          pageClass: "conceptiondetails",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/projectinfo",
        component: ProjectInfo,
        props: true,
        meta: {
          pageClass: "contentPage projectinfo",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/schedule",
        component: SchedulePage,
        props: true,
        meta: {
          pageClass: "schedule",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/statistics",
        component: StatisticsPage,
        props: true,
        meta: {
          pageClass: "statistics",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/survey",
        component: SurveyPage,
        props: true,
        meta: {
          pageClass: "contentPage surveyPage",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/custompage",
        component: CustomPage,
        props: true,
        meta: {
          pageClass: "contentPage surveyPage",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/dataprivacy",
        component: DataPrivacy,
        props: true,
        meta: {
          pageClass: "contentPage dataprivacy",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/imprint",
        component: ImprintPage,
        props: true,
        meta: {
          pageClass: "contentPage imprint",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/faq",
        component: FAQPage,
        props: true,
        meta: {
          pageClass: "contentPage faq",
          hasCreateButton: false
        },
        children: []
      },
      {
        path: "/contact",
        component: ContactPage,
        props: true,
        meta: {
          pageClass: "contentPage contact",
          hasCreateButton: false
        },
        children: []
      }
    ]
  });

// Locale Settings
i18next.init({
  // eslint-disable-next-line
  lng: LOCALE,
  resources: {
    en: {translation: translate.translationsEn},
    de: {translation: translate.translationsDe}
  },
  ignoreJSONStructure: false
});

Vue.filter("truncate", function (text, stop, clamp) {
  return text.slice(0, stop) + (stop < text.length ? clamp || "..." : "");
});

new Vue({
  el: "#app",
  name: "Main",
  router,
  store,
  i18n,
  components: {
    App
  },

  data () {
    return {
      isMobile: false,
      cookieBannerConfirmed: false,
      modalWasShown: false,
      showFilter: false,
      showCreateContributionModal: false
    };
  },

  watch: {
    $route (to, from) {
      this.$emit("routeChange", {from, to});
    }
  },
  created () {
    this.$store.watch((state, getters) => getters.projecttitle, (projectTitle) => {
      this.$title = projectTitle;
    });

    this.$store.dispatch("readTheming", configElement.src);
    const html = document.documentElement;
    // eslint-disable-next-line
    html.setAttribute("lang", process.env.VUE_APP_LANG);
  },

  mounted () {
    this.isMobile = this.$mediaBreakpointBetween("xs", "md");
    window.addEventListener("resize", this.handleResize);
  },

  methods: {
    handleResize: function () {
      this.isMobile = this.$mediaBreakpointBetween("xs", "md");
    },
    generateUUID: function () {
      return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
        const r = Math.random() * 16 | 0,
          // eslint-disable-next-line no-mixed-operators
          v = c === "x" ? r : r & 0x3 | 0x8;

        return v.toString(16);
      });
    },
    showErrorModal: function (headline, message) {
      const ModalInstance = new (Vue.extend(ModalElement))({parent: this}),
        imageElement = document.createElement("p"),
        headlineElement = document.createElement("h5"),
        messageElement = document.createElement("p");

      imageElement.className = "material-icons";
      imageElement.style = "width: 100%; text-align: center; font-size: 50px; cursor: default;";
      imageElement.innerHTML = "warning";
      headlineElement.innerHTML = headline;
      headlineElement.style = "font-weight: bold; text-align: center;";
      messageElement.innerHTML = message;

      ModalInstance.$mount();
      ModalInstance.$refs.content.appendChild(imageElement);
      ModalInstance.$refs.content.appendChild(headlineElement);
      ModalInstance.$refs.content.appendChild(messageElement);

      ModalInstance.$refs.closeButton.addEventListener("click", function () {
        this.$destroy();
        this.$el.remove();
      }.bind(ModalInstance));

      this.$el.appendChild(ModalInstance.$el);
    }
  },
  render: h => h(App)
}).$mount("#app");
