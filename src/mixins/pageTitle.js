export default {
  watch: {
    htmlPageTitle (val) {
      this.setHtmlPageTitle(val);
    }
  },
  methods: {
    setHtmlPageTitle (title) {
      if (title) {
        this.$title = title + " / " + this.$store.getters.projecttitle;
      }
      else {
        this.$title = this.$store.getters.projecttitle;
      }
    }
  },
  mounted () {
    this.setHtmlPageTitle(this.htmlPageTitle);
  }
};
