## Conventions for the VueJS part of the DIPAS project
### Drupal conventions see drupal.org
### Masterportal conventions see masterportal.org

The [masterportal conventions](https://bitbucket.org/geowerkstatt-hamburg/masterportal/src/dev/doc/conventions) are valid for DIPAS as well.

Additionally and possibly differently the following conventions are defined for DIPAS:

## Clean coding rules
Part B of the masterportal conventions is mandatory in DIPAS to improve code quality.

## File structur in frontend
For the Vue-framework several best practices are recommended. In this project the following of these recommendations are chosen to be used:

- Single-file component filename casing (STRONGLY RECOMMENDED): Filenames of component files shall be named in  PascalCase:
components/
|- MyComponent.vue (PascalCase)

- Multi-word component names (ESSENTIAL): Names of components (except "App") shall consist of minimally two words to avoid conflicts with html-elements that alwas consist of one word.
Vue.component('cp-item', {
export default {
name: 'TodoItem'

- Tightly coupled component names (STRONGLY RECOMMENDED): Structure and coupling of components shall be available from the file name. A senseful and ordered folder structure shall be used.
components/
|- TodoList.vue
|- TodoListItem.vue
|- TodoListItemButton.vue
components/
|- SearchSidebar.vue
|- SearchSidebarNavigation.vue

- Order of words in component names(STRONGLY RECOMMENDED): Order of words in the filename shall mirror the component structure.
components/
|- SearchButtonClear.vue
|- SearchButtonRun.vue
|- SearchInputQuery.vue
|- SearchInputExcludeGlob.vue
|- SettingsCheckboxTerms.vue
|- SettingsCheckboxLaunchOnStartup.vue

- Full-word component names (STRONGLY RECOMMENDED): The name of components shall consist for full words instead of abbreviations.
components/
|- StudentDashboardSettings.vue
|- UserProfileOptions.vue

For complex elements of the application a folder structure can be useful to approve readability.

### Avoid code duplications
Same code which is used in different components shall be outsourced to mixins.

### Comments on code

- VueStyleguidist and JSDoc are used in DIPAS
- methods, properties and components are commented in style of jsDoc and [VueStyleguidist](https://vue-styleguidist.github.io/docs/GettingStarted.html)
- start the documentation as live server: **npm run doc**
- build documentation as autarkic single site: **npm run doc:build** (the documentation will be stored in /dist-doc)

### License hints in the code

- we use a GPL license hint for javascript and PHP
- the standard format looks like this:

Example: JavaScript
````
/**
 * @license GPL-2.0-or-later
 */
````
Example: PHP
````
/**
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0-standalone.html GPL-2.0-or-later
 */
````

### General

- Each document shall be stored in utf-8 (without BOM)
- each document will end with an empty line
- Watch out for [***EditorConfig***](http://editorconfig.org/) settings
- code shall be documented inline as much as necessary to enlarge maintainability

### Error messages
We differ between error messages

- for debugging purposes: console.error and console.warn are allowed to log errors

- for information of the user: messages shall be understandable for the user and directly show up a solution

### Declarations
- *camelCase* for declaration of functions
- declaration of constans --> SYMBOLIC_CONSTANTS_LIKE_THIS
- no global variables are used

### CSS conventions
- avoid ID-selectors unless there is no other way out
- use a space after the selector
- rules are indended and written on several lines


"bad" example:
```css
.btn-panel-submit{background-color: #e6e6e6; border-color: #ccc; color: #333;}
```

"good" example:
```css
.print-tool > .btn-panel-submit {
    background-color: #e6e6e6;
    border-color: #ccc;
    color: #333;
}
```



### GIT

#### Commits
- language of the commits shall be english
- commit messages shall be prefixed with the ticket number (if there is one)

#### merge strategy
- use git rebase instead of git merge

#### branch naming conventions
- no GIT-flow strategy (no "feature/")
- branches shall be prefixed with the ticket number (if there is one)


### Definition of Done
- Code follows these conventions
- tests have been extended
- a test protocl has been added
- unit tests for new modules have been added
- German translations have been added for new UI developments
- function meets the requirements described in the ticket
- function has been tested in established browsers (FF, Chrome, Edge)
- function has been tested on mobile device or at least in mobile emulation of Chrome
- function and all its elements have been tested on DSGVO compliance
- function has been tested with "real life" data
- function has been tested in 1920x1280 and 150% display zoom 
- pull request contains a description how the function of the ticket can be tested and approved
