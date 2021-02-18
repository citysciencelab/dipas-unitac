/**
 * @license GPL-2.0-or-later
 */

<script>
export default {
  name: "StatisticsTopNodeList",
  props: {
    headline: {
      type: String,
      default: ""
    },
    nodeList: {
      type: Array,
      default () {
        return [{}];
      }
    },
    routerlink: {
      type: String,
      default: ""
    }
  }
};
</script>

<template>
  <div class="nodeTopList">
    <p class="headline">
      {{ headline }}
    </p>
    <div class="listing">
      <ol start="9">
        <li
          v-for="node in nodeList"
          :key="node.nid"
        >
          <router-link :to="routerlink + node.nid">
            {{ node.title }}
          </router-link>
        </li>
      </ol>
    </div>
  </div>
</template>
