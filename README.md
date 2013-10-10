# Qissues
Qissues is a command-line interface for interacting with various bug tracking systems. The goal is a single, sane interface rather jumping back and forth between slow GUIs.

## Connectors
Currently, only two trackers are supported:

- Bitbucket Issues
- JIRA

I've also began work on a Trello connector, but it's nowhere near ready for use.

# Setup

Clone the repository, and you can install it one of two ways:

## PHAR (recommended)

It's recommended you compile a phar archive, and either move it within your PATH.

    bin/compile
    chmod +x qissues.phar
    sudo mv qissues.phar /usr/bin/qissues

## Run it manually

`/path/to/repository/bin/qissues`
