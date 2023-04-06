/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the data of the project schedule page
 * @displayName SchedulePage
 */
import moment from "moment";
import {requestBroker} from "../../mixins/requestBroker.js";
import ScheduleItem from "./components/ScheduleItem.vue";
import RightColumn from "../../basicComponents/RightColumn.vue";

export default {
  name: "SchedulePage",
  components: {
    ScheduleItem,
    RightColumn
  },
  mixins: [requestBroker],
  data () {
    return {
      schedule: {
        links: {},
        nodes: [],
        pager: {
          currentPage: 1,
          totalPages: 1,
          itemsPerPage: 10
        },
        totalNodes: 0
      }
    };
  },
  computed: {
    htmlPageTitle () {
      return this.$t("Schedule.title");
    },
    activeAppointments () {
      return this.schedule.nodes.filter(appointment => moment(appointment.expires).isSameOrAfter(moment()));
    }
  },
  beforeMount () {
    /**
     * loads initally the schedule data object from requestbroker drupal api
     * @returns {void}
     */
    this.loadSchedule();
  }
};
</script>

<template>
  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7 col-xl-7 schedule">
        <h1>{{ $t("Schedule.headline") }}</h1>

        <div class="scheduleList">
          <!--
            @name ScheduleItem
            @property {Array} activeAppointments
          -->
          <ScheduleItem
            v-for="appointment in activeAppointments"
            :key="appointment.nid"
            :appointment="appointment"
          />

          <p v-if="!activeAppointments.length">
            {{ $t("Schedule.nodeLength") }}
          </p>
        </div>
      </div>

      <div class="col-md-1 col-1"></div>
      <!--
        @name RightColumn
      -->
      <RightColumn class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4" />
    </div>
  </div>
</template>

<style>
    #app.mobile div.schedule h1 {
        padding-top: 35px;
    }

    div.schedule h1 {
        font-size: 2.25rem;
        font-weight: bold;
        color: #003063;
        word-break: break-word;
    }
</style>
