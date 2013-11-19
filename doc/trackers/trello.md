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
An issue repository doesn't map onto Trello 100%, so there is some oddness to figure out.

## Querying
`GET /1/boards/[idBoard]/cards`
We'll have to do in-memory filtering most of the time due to limitations in the Trello API.

**Statuses**
`GET /1/lists/[idList]/cards`
Multiple throws exception

**Ids**
`GET /1/search?idCards=1,2,3`
technically supported, but need to convert numbers to shortIds

**Keyword**
`GET /1/search?query=keywords`
Removes support for status and member assignee.

**Assignees**
TODO

**Types**
not supported

**Priorities**
not supported (default sort is prioritized, basically)
