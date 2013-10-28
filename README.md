# Qissues
Qissues is a command-line interface for interacting with various bug tracking systems. The goal is a single, sane interface rather jumping back and forth between slow GUIs.

## Connectors
Currently, only two trackers are supported:

- Bitbucket Issues
- JIRA

I've also began work on a Trello connector, but it's nowhere near ready for use.

# Features

- **Query** - Query your repository with various options, and see results in your terminal.
- **Reports** - Save common queries as reports so you can re-run them later. Ex: `qissues -r bugs`
- **Views** - Supports alternate views (large table view, smaller list with metadata, and tiny list). Automatically detects, but can be overridden.
- **Modify Issues** - Submit and modify reports from the terminal, launching the editor of your choice.
- **Git Integration** - Easily query active issue based on branch name. "feature-x"

# Setup

Clone or download the repository, and run `bin/install`. This will compile the software, and place it in your user's bin directory. 

If you don't already have `~/bin` in your path, you can add it via:

    export PATH="$PATH":~/bin

After that, running `qissues` should show the information.

# Project Configuration

To use qissues with a project, create a `.qissues` file in the root of it. It's also a good idea to add it to your global .gitignore. This is a YML format, and requires at the very least a connector:

Example Configuration:

    connector: JIRA
    jira:
        project: projectname
        prefix: PREFIX
        username: youraccountname
        password: "your password"

You can also move any common configuration up to a `~/.qissues` file to avoid having to copy your credentials each time.
