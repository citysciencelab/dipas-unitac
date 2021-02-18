> Now enter some data to see the DIPAS user frontend working

# Step by step

[TOC]

## Who can configure what

### Different roles: project admin
- Create a new user for the project administration with role **Project administrator**
- Users of this role can be assigned to one or more proceedings, designed by the technical admin
- This role has rights to
	- create content
	- create taxonomies for the categories and rubrics
	- configure DIPAS settings
	- create masterportal instances and relate them to DIPAS settings

### Different roles: technical admin
- The technical admin has all rights of a drupal admin
- In addition to the project admin privileges, they can primarily manage following DIPAS related settings
	- configure masterportal settings and create default masterportal instances for DIPAS
	- activate keyword service
  - create new proceedings and grant permission for project admins to single or multiple proceedings

## Translate the user interface
- If necessary you can now import the german translation which comes with the download of dipas
- at /admin/config/regional/translate/import you can import the file de.po which is located in your htdocs folder at drupal/config/
- choose all three checkboxes and then press import

## Enter a first set of data

### Using DIPAS with the domain module
In the basic configuration DIPAS ist set up to use the domain module.
This allows to set up several proceedings parallely without effort and manage the access rights to these proceedings easily.

The first step is to set up the default domain.

### Set up the default domain
It is important to flush all drupal caches after first installation on a new system, so that the custom form to create proceedings in DIPAS is applied.
After that open /admin/config/user-interface/proceedings to add a new proceeding. As this is the first one, it will automatically be the default domain and only some data are required.

All content, added to the default domain will be the basic of a new proceeding.

### Minimal content of a proceeding
**What you need to enter minimally is**

- Taxonomy for categories
    - Some icons can be found under the folder drupal/modules/custom/dipas/assets in the repository
    - These icons are used for the contribution categories
- Taxonomy for rubrics
- Page for the project information
- Page for the disclaimer
- Page for the impressum
- Page for the contact
- Page for the frequently asked questions

### DIPAS configuration
**Then you should configure the DIPAS settings found at ***/config/user-interface/dipas*** to meet your requirements**

- *Project information*
    - holds the general information of the project, like
        - project title
        - contact information
        - logos
- *Project schedule*
    - project phase
        - phase 1
          - the phase where users can add contributions to the configured project
          - the contributions can be commented and rated
          - configure project period for phase 1
        - phase 2 can be activated separatly
          - the phase where users can inform about conceptions
          - the conceptions can be commented
          - configure the drupal page where the conceptions are combined to be presented as frontpage
- *Project area*
    - Defines this project area boundary for use in map displays. The boundary is digitized in a map application. Click *Create project area* to start the digitization.
- *Contributions*
    - configures details on the contributions created by the public users
    - defines which masterportal instances are used for the different pages
- *Sidebar settings*
    - allows to configure which blocks shall be shown on the sidebar of the frontend
- *Menu settings*
    - allows to configure which menu items shall be available including
       - titel of the menu item
       - icon shown in front of the name and on the mobile footer menu
       - page which shall be displayed, when the menu item is selected
          - here you choose, which of your created pages (see point 1) shall be shown where
    - a custom page, additionally to the proposed ones, can be configured as well
- *Keyword settings*
    - if you activate keyword processing the user's contribution description will automatically be sent to a keyword service. In the following contribution wizard page the keywords will be given to the user who can choose them for the contribution
- *NLP Settings*
    - DIPAS experimentally includes a natural language processing which allows automated text analysis
    - this can be configured here
- *Data export*
    - When the proceeding is finished you can export the contribution data and here

### Change Drupal Configuration
**Changing the drupal configurations with drush**

- import the actual configurations **!!BEFORE!!** you change the configuration. (drush cim)
- ensure language is set to **english**
- make your changes
- export the new configuration (drush cex)
- Move all files from /config/export to /config/sync
- use "git add && git commit" to apply changes

### Cron
**Configure Cron**

- as last point you need to configure cron
   - you find the external URL for the drupal cron at ***Configuration/System/Cron***
   - take the cryptic URL and add it to a scheduled task (windows) or a cron job (linux) to run the drupal cron regularly (~every 10 minutes)
- cron will do several things
   - clean your temporary files and vacuum the database
   - check whether your project is still in the active phase (configured in your project information settings)
   - when the project period is over, drupal will automatically close contributions and rating and change the start page for the public useres
   - if NLP is configured, cron will check if contributions have changed and will then recalculate the NLP data of your project
