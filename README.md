# Qissues
Qissues is a CLI tool for interacting with various bug tracking systems. It's meant to provide a single, sane interface that is quick to use and can keep your hands on the keyboard. It's also heavily customizable to accomodate whatever workflow you have.

Qissues was inspired by Mylyn for Eclipse, but meant to assist people who prefer working with terminal applications rather than IDEs. Basically, quick read/write access to your issues without having to change programs.

## Issue Trackers
The system is set up to help abstract and normalize the differences between various systems. It was modelled by looking at a handful of systems and researching even more. Therefore, adding new trackers shouldn't take too much work.

Currently, we supported:

- GitHub
- Bitbucket Issues

With immediate plans to support:

- JIRA (WIP)
- Trello (Lists as Statuses)

## Features

- **Query** - Query your repository with various options, and see results in your terminal.
- **Reports** - Save common queries as reports so you can re-run them later. Ex: `qissues -r bugs`
- **Views** - Supports alternate views (large table view, smaller list with metadata, and tiny list). Automatically detects, but can be overridden.
- **Post/Edit Issues** - Submit and modify issues from comfort of your own terminal.
- **Git Integration** - Easily query active issue based on branch name. "feature-x", would allow `qissues view`, `qissues edit`, without remembering 'x'.

## Installation

Clone or download the repository, and run `make`. This will install it in the current directory to play with. To make it globally available, run `make install`.

If you don't already have `~/bin` in your path, you can add it via:

    export PATH="$PATH":~/bin

After that, running `qissues` from the root of a project.

## Project Setup

To use qissues with a project, create a `.qissues` file in the root of it. It's also a good idea to add it to your `~/.gitignore`. The config file is YML format, and you can see a configuration reference by looking at the `config/*.yml files`.

Example Configuration:

    tracker: github
    github.repository: "AdrianSchneider/Qissues"
    github.username: "YourAccount"
    github.password: "YourPassword"

It's advised to also create a `~/.qissues` file for credentials and non project-specific configuration.
