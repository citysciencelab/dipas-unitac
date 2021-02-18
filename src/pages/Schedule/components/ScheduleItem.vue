/**
 * @license GPL-2.0-or-later
 */

<script>
import _ from "underscore";
import moment from "moment";
import Masterportal from "../../../basicComponents/Masterportal.vue";

export default {
  name: "ScheduleItem",
  components: {
    Masterportal
  },
  props: {
    appointment: {
      type: Object,
      default () {
        return {};
      }
    }
  },
  data () {
    return {
      showDetails: false
    };
  },
  computed: {
    appointmentDate () {
      let appointmentDate = moment(this.appointment.start).format("DD.MM.YYYY - HH:mm");

      if (this.appointment.end) {
        if (moment(this.appointment.start).get("day") === moment(this.appointment.end).get("day")) {
          appointmentDate += " bis " + moment(this.appointment.end).format("HH:mm") + " Uhr";
        }
        else {
          appointmentDate += " Uhr bis " + moment(this.appointment.end).format("DD.MM.YYYY - HH:mm") + " Uhr";
        }
      }
      else {
        appointmentDate += " Uhr";
      }
      return appointmentDate;
    },
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
    @click="showDetails = true"
  >
    <h2>{{ appointment.title }}</h2>
    <p class="topic">
      <label>{{ $t("Schedule.Item.topic") }}</label> {{ appointment.topic }}
    </p>
    <p class="detailLink">
      {{ $t("Schedule.Item.details") }}<i class="material-icons">play_arrow</i>
    </p>
    <hr />
    <p class="address">
      {{ appointment.organizer }}
    </p>
    <p class="appointmentDate">
      {{ appointmentDate }}
    </p>

    <ModalElement
      v-if="showDetails"
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
        padding: 20px;
        margin-bottom: 10px;
    }

    section.appointment h2 {
        font-size: 20px;
        font-weight: bold;
        color: #003063;
        padding: 0;
        margin: 0 0 5px 0;
    }

    section.appointment p {
        font-size: 13px;
        padding: 0;
        margin: 0 0 5px 0;
    }

    section.appointment p.address,
    section.appointment p.appointmentDate {
        font-size: 12px;
        font-weight: bold;
        line-height: 12px;
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
        line-height: 10px;
        font-size: 10px;
        margin: 0;
        padding: 0;
        white-space: nowrap;
    }

    section.appointment p.detailLink {
        cursor: pointer;
        color: #2573B4;
    }

    section.appointment:hover p.detailLink,
    section.appointment p.detailLink:hover {
        text-decoration: underline;
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
        font-size: 13px;
        line-height: inherit;
        font-weight: normal;
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>

