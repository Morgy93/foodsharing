# About the devdocs

Feel free to provide feedback or ask questions at the #foodsharing-dev [Slack](https://slackin.yunity.org/) channel at any time.

## General information

The developer documentation (devdocs) contains general information about the foodsharing website project, step-by-step instructions, and references.

## Target groups of the devdocs

The devdocs should offer everything newcomers need to start participating in the foodsharing website project.

The devdocs should also be of help to anyone that got stuck while working on the foodsharing website project and is in need of help.

## Contributing to the devdocs

Anyone can contribute to the devdocs. The git project folder is called `docs`.

The devdocs are based on the following principles:

- Information should be correct and current
- Information should be consistent (language, formatting,...)
- Information should be concise (but some repetition is necessary)
- Information should be complete (probably an infeasible ideal)

But don't worry too much about the last three principles.
<!-- There are people solely dedicated to improving the devdocs. -->

### What in, what out?

How do I decide if a specific information should be in the devdocs?
Here are some principles:
- Is the information specific to foodsharing? -> Yes
- Were you surprised about it? -> Yes
- Is the information general enough to be explained in a foodsharing-independent tutorial or documentation? -> No, include a link
- Is the information specificly about one piece of code and only interesting during reading this code (and not for finding this piece of code)? -> No, explain the code with comments in the code.

### Markdown

The devdocs are written in [Markdown](https://toolchain.gitbook.com/syntax/markdown.html) (md).
Additionally we use the plugin [richquotes](https://github.com/erixtekila/gitbook-plugin-richquotes).

### Setting things up

The devdocs are built at every change on the master branch and published [here](https://devdocs.foodsharing.network).
To see your changes, you can build the devdocs locally.
You need to have `nodejs` and `yarn` installed on your system. On Windows: After every install you might have to close and reopen the powershell so yarn is recognized as a command. Then go to the /docs folder and

Run
```
yarn global add gitbook-cli
gitbook install docs
cd ..
gitbook serve docs --port 4001
```
This makes the current devdocs avaiable via `localhost:4001` in your browser.

**It is updated at every change of the files. You need to send the `gitbook serve docs --port 4001` again when you save a changed file**

It would be nice to have a docker setup (to avoid the local `yarn` dependency). Please document it here if you set it up.

The gitlab ci is not triggered if you push with the option `git push -o ci.skip`.
This is useful if you work on the devdocs since they are only built on master anyway.

## GitBook

The devdocs are built with [GitBook](https://docs.gitbook.com/).
