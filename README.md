# Qissues
Qissues is a command-line interface for interacting with various bug tracking systems. The goal is a single, sane interface rather jumping back and forth between slow GUIs.

## Issue Trackers
- GitHub
- Bitbucket Issues (WIP)
- JIRA (WIP)

## Features

- **Query** - Query your repository with various options, and see results in your terminal.
- **Reports** - Save common queries as reports so you can re-run them later. Ex: `qissues -r bugs`
- **Views** - Supports alternate views (large table view, smaller list with metadata, and tiny list). Automatically detects, but can be overridden.
- **Modify Issues** - Submit and modify reports from the terminal, launching the editor of your choice.
- **Git Integration** - Easily query active issue based on branch name. "feature-x"

## Installation

Clone or download the repository, and run `make install`. This will install it in the current directory to play with. To make it globally available, run `make install`.

If you don't already have `~/bin` in your path, you can add it via:

    export PATH="$PATH":~/bin

After that, running `qissues` from the root of a project.

## Project Setup

To use qissues with a project, create a `.qissues` file in the root of it. It's also a good idea to add it to your `~/.gitignore`. The config file is YML format, and you can see a configuration reference by looking at the `config/*.yml files`.

Example Configuration:

    tracker: github
    github:
        repository: "AdrianSchneider/Qissues"
        username: "YourAccount"
        password: "YourPassword"

It's advised to also create a `~/.qissues` file for credentials and non project-specific configuration.
