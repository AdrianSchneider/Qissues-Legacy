# Setup
Trello requires some manual set up, hopefully somewhat automated later...

## 1: Appliation Key
Head to `https://trello.com/1/appKey/generate` and copy the top key. Save that in `trello.key`.

## 2. Authorize Application
Open the following URL in an authenticated browser session, replacing [key] with your key from above.
    https://trello.com/1/authorize?callback_method=fragment&return_url=&scope=read,write&expiration=never&name=Qissues&key=[key]

Allow access, when prompted. Copy the token from the URL afterward and configure it as `trello.token`.

Finally, fill in `trello.board` with the **name** of the board you wish to use as a repository.

## 3. Grab Metadata
Run `qissues refresh` to grab the latest metadata from Trello.

# Mapping
Trello isn't an issue tracker, so some concepts don't translate perfect. It's a short term issue tracker, not a long term data repository. I tried to model it with that in mind, focusing on sprints or a more casual usage.

- new issues default to furthest left List (status)
- closing an issue archives it
- use change-status to push status changes
- checklists are read-only, for now
- most of the querying is done in memory, API is too restricted
- priority is 'top' or 'bottom'; omitting will save new ones as 'bottom', and leave existing as-is
- priority when viewing is just the natural order in Trello
- we can only use one (the first) assignee, so run-time filtering may not work correctly for issues with multiple assignees
