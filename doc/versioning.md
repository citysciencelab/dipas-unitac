## Versioning
The DIPAS project uses the semantic versioning scheme.

`MAJOR`.`MINOR`.`PATCH`

The project starts with the version `0.1.0` before the open sourcing.
Since version `2.0.0` the domain-module is included.

## Release cycle
Minor updates will be released after every sprint.
Before the release of the minor update, there will be at least one release-candidate marked as `*.*.*-rc`.
The release candidate shall be tested manually in a staging environment.
If no bugs were found, or they were addressed with a hotfix, the version will be finally released.
If necessary patch updates can be released between sprints.

## Breaking changes
Both internal and external APIs are in the scope for breaking changes, which will cause a major update.
At the internal layer both our own and 3rd-party code needs to be backwards compatible till the next major update.
For example the update to the next Drupal Major will be a break as well as the update of the vue major for the frontend.

## Release workflow
Each version has to be created in Jira. At latest, it shall be created during the sprint planing. All features and bugs
intended to be completed in the planed sprint shall be marked with the new version. This includes tickets that are added to the sprint and unfinished tickets from the last sprint.

### Release Ticket
The release ticket is the last step of a version, it shall be the last ticket of that version to be finished.
The Scrum Master is responsible for the creation and handling of this ticket.
This ticket shall be closed after the final version has been tagged in git.

### Release finalizing
After the release ticket has been closed, there shouldn't be any other unfinished tickets assigned to this version.
Otherwise, they can be automatically reassigned to another version in the release process.
To release a version, the last step is to release the version in Jira.
After the version has been released, the generated release notes for this version can be viewed and copied.


## Branching
We use `prodcution`, `dev`, `DPS-*` and `hotfix/DPS-*` branches.
Releases and release candidates will be tagged on the `prodcution` branch. The `dev` branch is the main development line.
All `DPS-*` branches must be branched of the `dev` branch. Only `DPS-*` branches for subtasks, are branched of the `DPS-*` branch of the parent ticket. All branches must be merged back into the branch they were branched from.
All merges are done via pull requests and fast forward merge strategy.
If it's time to create a release candidate, the `dev` branch will be merged into the `production` branch.
The created merge commit must be tagged as `*.*.*-rc`. If bugs are found during testing of the release candidate,
they should be fixed in `hotfix/DPS-*` branches that origin from the `production` branch and be merged back into the
`production` branch. Hotfix branches are the only prefixed branches to indicated that they're branched from the `productuion` branch and must be merged back into the same. After the merge the bugfix must be cherry picked back to the `dev` branch. This is the only time a commit can directly be pushed to the `dev` branch.
If needed another released candidate can be tagged (as `*.*.*-rc1`). When the release candidate is final it should be tagged as final version with `*.*.*`. A commit can have multiple tags, that makes it possible to tag the last release candidate as final release.

![picture](/img/git_branching.svg)

## Pipelines
Bitbucket pipelines are used to generate deployable artifacts in the form of a zip archive. The pipeline runs for all tagged commits. The tag is used to identify the generated artifact, it's part of the file name and can be found in the `VERSION.txt` file in the archive.

