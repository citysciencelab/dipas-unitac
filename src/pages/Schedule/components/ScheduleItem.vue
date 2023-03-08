/**
 * @license GPL-2.0-or-later
 */

<script>
/**
 * Serves the data of a single schedule item
 * @displayName ScheduleItem
 */
import _ from "underscore";
import moment from "moment";
import Masterportal from "../../../basicComponents/Masterportal.vue";

export default {
  name: "ScheduleItem",
  components: {
    Masterportal
  },
  props: {
    /**
     * single appointment item
     */
    appointment: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      showDetails: false,
      rndid: "id" + Math.floor(Math.random() * 100000000)
    };
  },
  computed: {
    /**
     * serves the date of the single appointment item
     * @returns {String} appointment date
     */
    appointmentDate () {
      // TODO time
      let appointmentDate = moment(this.appointment.start).format("DD.MM.YYYY - HH:mm");

      if (this.appointment.end) {
        if (moment(this.appointment.start).get("day") === moment(this.appointment.end).get("day")) {
          // TODO time
          appointmentDate += " " + this.$t("Schedule.Item.until") + " " + moment(this.appointment.end).format("HH:mm") + " " + this.$t("Schedule.Item.oClock");
        }
        else {
          // TODO time
          appointmentDate += " " + this.$t("Schedule.Item.oClock") + " " + this.$t("Schedule.Item.until") + " " + moment(this.appointment.end).format("DD.MM.YYYY - HH:mm") + " " + this.$t("Schedule.Item.oClock");
        }
      }
      else {
        appointmentDate += " " + this.$t("Schedule.Item.oClock");
      }
      return appointmentDate;
    },
    /**
     * serves the localization of a single appointment item
     * @returns {String|Boolean} appointment date
     */
    appointmentLocalization () {
      if (!_.isNull(this.appointment.lon) && !_.isNull(this.appointment.lat)) {
        const coords = this.appointment.lon + "," + this.appointment.lat;

        return this.$store.getters.schedulemap + "?projection=EPSG:4326&marker=" + coords + "&center=" + coords;
      }
      return false;
    }
  }
};
</script>

<template>
  <section
    class="appointment"
    tabindex="0"
    @click="showDetails = true"
    @keyup.enter="showDetails = true"
  >
    <h2
      :id="rndid"
    >
      {{ appointment.title }}
    </h2>
    <p class="topic">
      <label>{{ $t("Schedule.Item.topic") }}</label> {{ appointment.topic }}
    </p>
    <p
      class="detailLink"
      role="link"
      :aria-describedby="rndid"
    >
      {{ $t("Schedule.Item.details") }}<i class="material-icons">play_arrow</i>
    </p>
    <hr />
    <p class="address">
      {{ appointment.organizer }}
    </p>
    <p class="appointmentDate">
      {{ appointmentDate }}
    </p>
    <!--
      @name ModalElement
      @fire closeModal
    -->
    <ModalElement
      v-if="showDetails"
      role="dialog"
      @closeModal="showDetails = false"
    >
      <div class="scheduleDetails">
        <h2>{{ appointment.title }}</h2>

        <p class="topic">
          <label>{{ $t("Schedule.Item.topic") }}</label> {{ appointment.topic }}
        </p>

        <p class="appointmentDate">
          {{ $t("Schedule.Item.date") }}<br />{{ appointmentDate }}
        </p>

        <p
          class="description"
          v-html="appointment.description"
        >
        </p>

        <hr />

        <p class="address">
          {{ appointment.organizer }}<br />
          {{ appointment.street1 }}<br />
          <template v-if="appointment.street2.length">
            {{ appointment.street2 }}<br />
          </template>
          {{ appointment.zip }} {{ appointment.city }}
        </p>
        <!--
          @name Masterportal
        -->
        <Masterportal
          v-if="appointmentLocalization"
          :src="appointmentLocalization"
        />
      </div>
    </ModalElement>
  </section>
</template>

<style>
    section.appointment {
        background-color: #F0F0F0;
        padding: 32px;
        margin-bottom: 10px;
    }

    section.appointment h2 {
        font-size: 1rem;
        font-weight: bold;
        color: #003063;
        padding: 0;
        margin: 0 0 5px 0;
    }

    section.appointment p {
        font-size: 1rem;
        padding: 0;
        margin: 0 0 5px 0;
    }

    section.appointment p.address,
    section.appointment p.appointmentDate {
        font-size: 0.8rem;
        font-weight: bold;
        line-height: 0.9rem;
        color: black;
    }

    section.appointment hr {
        border-color: #C3C3C3;
        padding: 0;
        margin: 0 0 10px 0;
    }

    section.appointment p.detailLink,
    section.appointment p.detailLink i {
        vertical-align: middle;
        text-align: right;
        line-height: 2rem;
        font-size: 0.8rem;
        margin: 0;
        padding: 0;
        white-space: nowrap;
    }

    section.appointment p.detailLink {
        cursor: pointer;
        color: #005CA9;
        font-weight: bold;
    }

    section.appointment div.customModal div.scheduleDetails {
        width: 70vh;
    }

    #app.mobile section.appointment div.customModal div.scheduleDetails {
        width: 100%;
    }

    section.appointment div.customModal div.scheduleDetails hr {
        margin-top: 10px;
    }

    section.appointment p.description {
      max-height: 15vh;
      overflow-y: auto;
    }

    section.appointment div.customModal div.scheduleDetails p.address {
        font-size: 1rem;
        line-height: inherit;
        font-weight: normal;
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>

