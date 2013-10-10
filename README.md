# Qissues
Qissues is a command-line interface for interacting with various bug tracking systems. The goal is a single, sane interface rather jumping back and forth between slow GUIs.

## Connectors
Currently, only two trackers are supported:

- Bitbucket Issues
- JIRA

I've also began work on a Trello connector, but it's nowhere near ready for use.

# Features

**Query** - Query your repository with various options, and see results in your terminal.
**Reports** - Save common queries as reports so you can re-run them later. Ex: `qissues -r bugs`
**Views** - Supports alternate views (large table view, smaller list with metadata, and tiny list). Automatically detects, but can be overridden.
**Modify Issues** - Submit and modify reports from the terminal, launching the editor of your choice.
**Git Integration** - Easily query active issue based on branch name. "feature-x"

# Setup

Clone the repository, and you can install it one of two ways:

## PHAR (recommended)

It's recommended you compile a phar archive, and either move it within your PATH.

    bin/compile
    chmod +x qissues.phar
    sudo mv qissues.phar /usr/bin/qissues

## Run it manually

`/path/to/repository/bin/qissues`
