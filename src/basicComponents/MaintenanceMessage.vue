/**
 * @license GPL-2.0-or-later
 */

<script>
export default {
  name: "MaintenanceMessage",
  props: {
    message: {
      type: String,
      default: "",
      required: true
    }
  }
};
</script>

<template>
  <div class="maintenance">
    <div class="maintenance-message-container">
      <img
        class="maintenance-image"
        src="../assets/maintenance.svg"
        width="100"
      />
      <h1>{{ $t("MaintenanceMessage.headline") }}</h1>
      <hr>
      <span>{{ message }}</span>
    </div>
  </div>
</template>

<style>
.maintenance {
    background-color: cadetblue;
    height: 100vh;
    width: 100%;
    background: rgb(255,255,255);
    background: -moz-linear-gradient(top,  rgba(255,255,255,1) 0%, rgba(215,222,227,1) 98%);
    background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 0%,rgba(215,222,227,1) 98%);
    background: linear-gradient(to bottom,  rgba(255,255,255,1) 0%,rgba(215,222,227,1) 98%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#d7dee3',GradientType=0 );
}
.maintenance-message-container {
    position: absolute;
    padding: 20%;
    margin: auto;
    top: 180px;
    left: 0;
    right: 0;
    text-align: center;
    color: #696969;
}
.maintenance-image {
    position: absolute;
    margin: auto;
    top: -60px;
    left: 0;
    right: 0;
}
</style>
