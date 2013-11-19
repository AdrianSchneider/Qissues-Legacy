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
