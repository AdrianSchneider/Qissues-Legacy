# Qissues [![Build Status](https://travis-ci.org/AdrianSchneider/Qissues.png?branch=master)](https://travis-ci.org/AdrianSchneider/Qissues)
Qissues is a CLI tool for interacting with various bug tracking systems. It's meant to provide a single, sane interface that is quick to use and can keep your hands on the keyboard. It's also heavily customizable to accomodate whatever workflow you have.

Qissues was inspired by Mylyn for Eclipse, but meant to assist people who prefer working with terminal applications rather than IDEs. Basically, quick read/write access to your issues without having to change programs.

## Issue Trackers
The system is set up to help abstract and normalize the differences between various systems. It was modelled by looking at a handful of systems and researching even more. Therefore, adding new trackers shouldn't take too much work.

Currently supported:

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

After that, run `qissues` from the root of a project.

## Project Setup

To use qissues with a project, create a `.qissues` file in the root of it. It's also a good idea to add it to your `~/.gitignore`. The config file is YML format, and you can see a configuration reference by looking at the `config/*.yml files`.

Example Configuration:

    tracker: github
    github.repository: "AdrianSchneider/Qissues"
    github.username: "YourAccount"
    github.password: "YourPassword"

It's advised to also create a `~/.qissues` file for credentials and non project-specific configuration.

## Usage

After configuring a project,

**Query issues**:

    qissues [-r report [-s status [-t type [-a assignee [-k keyword [-l label [-o sortfield [--limit amt [--page num]]]]]]]]]

Will output something similar to:

    +-----------------------------------------------------------------------------------
    | Id | Title                                       | Status   | Date updated       |
    +-----------------------------------------------------------------------------------
    | 1  | Dividing by zero upgrades PHP               | new      | 2013-11-03 7:01pm  |
    | 2  | Qissues doesn't work in Python              | new      | 2013-11-03 7:00pm  |
    | 3  | Insert coin here does not accept bitcoin    | new      | 2013-11-03 6:57pm  |
    | 4  | Working with JIRA is frustrating            | resolved | 2013-04-16 2:36am  |
    +-----------------------------------------------------------------------------------

Save common queries to `.qissues` as reports.

**View an Issue**: Once you know what you're looking for, you can view more details:

    qissues view 4

**Create Issue**: You can create new issues from Qissues many ways. The default launches $EDITOR with an empty frontmatter document.

    qissues create

Looks like this...

    ---
    title:
    labels:
    assignee:
    ---

**Edit an Issue**: Works similar to create, but pre-populated.

    qissues edit 6

Looks like...

    ---
    title: The Internet is DOWN!
    assignee: AdrianSchneider
    labels: emergency, lol
    ---
    The internet is currently down. I cannot post TPS reports.

Save and close.

## Documentation
There is a lot more to learn in the [Docs](doc/). Nearly every aspect is customizable or extendable, so take a peek.
