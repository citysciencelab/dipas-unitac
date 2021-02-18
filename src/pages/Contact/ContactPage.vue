/**
 * @license GPL-2.0-or-later
 */

<script>
import {requestBroker} from "../../mixins/requestBroker.js";
import ContentPage from "../ContentPage/ContentPage.vue";
import ContactBlock from "../../basicComponents/ContactBlock.vue";

export default {
  name: "ContactPage",
  components: {
    ContactBlock
  },
  extends: ContentPage,
  mixins: [requestBroker],
  beforeMount () {
    this.loadEndpoint("contact");
  }
};
</script>

<template>
  <div class="container">
    <div class="row">
      <div class="col-sm-7 col7">
        <h1>{{ pageContent.title }}</h1>

        <component
          :is="getComponent(element)"
          v-for="element in pageContent.content"
          :key="getComponent(element).name"
          :content="element"
        />

        <ContactBlock class="contactComponent" />
      </div>

      <div class="col-sm-1 col1"></div>

      <RightColumn class="col-sm-4 col4" />
    </div>
  </div>
</template>

<style>
    #app.mobile div.row h1 {
        padding-top: 35px;
    }

    .contactComponent .headline,
    .contactComponent .projectOwner {
        font-size: 32px;
        color: #003063;
        font-weight: bold;
    }

    .contactComponent .projectOwner {
        font-size: 20px;
    }

    div.row h1 {
        font-size: 36px;
        font-weight: bold;
        color: #003063;
    }
</style>
