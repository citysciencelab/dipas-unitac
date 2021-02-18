// set your global autonav options - override in page frontmatter `autonav`
const autonav_options = {
  enable: true
};

module.exports = {
  title: 'DIPAS Documentation',
    themeConfig: {
      logo: '/dipaslogo.jpg',
      search: true,
      sidebar: 'auto',
      smoothScroll: true,
      nav: [
        {
          text: 'App Structure',
          link: '/structure.md'
        },
        { text: 'Setup Dipas', link: '/step_by_step.md' },
        {
          text: 'User Manual',
          ariaLabel: 'User Manual',
          items: [
            { text: 'Contribute', link: '/contribute.md' },
            { text: 'Contribution as Admin', link: '/contibution_admin.md' },
            { text: 'Contribution as Project Admin', link: '/contibution_projectadmin.md' }
          ]
        },
        {
          text: 'Coding Dipas',
          ariaLabel: 'Coding Dipas',
          items: [
            { text: 'Setup the development environment', link: '/setup_dev_environment.md'},
            { text: 'Setup Drupal Backend', link: '/first_steps.md' },
            { text: 'Masterportal in Drupal', link: '/masterportal.md'},
            { text: 'Using AddOns', link: '/addons.md' },
            { text: 'Using Drupal REST API', link: '/DrupalRestAPI.md' },
            { text: 'Coding Conventions', link: '/conventions.md' }
          ]
        },
        {
          text: 'Versioning',
          ariaLabel: 'Versioning',
          items: [
            { text: 'Versioning', link: '/versioning.md' }
          ]
        },
        {
          text: 'Tipps & Tricks',
          items: [
            { text: 'PostgreSQL installation problems', link: '/troubleshooting.md'}
          ]
        }
      ]
    },
    navbar: "auto"
  }