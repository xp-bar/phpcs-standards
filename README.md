# PHPCS Standards - The XpBar edition

These are my personal phpcs standards, a collection of sniffs and rulesets from others, and some I've written myself.

## PREFACE

This repo contains both my own work, and the works of [Squiz Labs](https://github.com/squizlabs/PHP_CodeSniffer) and [Slevomat](https://github.com/slevomat/coding-standard), and uses phpcs Sniffs written by multiple other parties.

Repositories for sniffs not included in the default set provided by Squiz Labs:
- [slevomat/coding-standard](https://github.com/slevomat/coding-standard)
- [hostnet/phpcs-tool](https://github.com/hostnet/phpcs-tool)
- [sirbrillig/variable-analysis](https://github.com/sirbrillig/phpcs-variable-analysis)
- [xp-bar/phpcs-standards](#)

## INSTALLATION

*Before Starting, make sure:*
1. you can clone git objects via the command line with `git clone`
2. `which phpcs` should return a path if you have previously installed `phpcs`
3. you have a global installation of composer

All you should need to get started is the install script; simply

1. Clone the Repo
2. cd into the directory
3. (optional) `cat install.sh` -- check the source code, make sure it's safe!
4. `chmod +x ./install.sh`
5. run `./install`
6. (optional) run `./update` to update! (re-clones the repos and re-installs sniffs)
7. update your editor config to point to your global phpcs installation - `which phpcs` and to use the `XpBar` standard as the default
8. (optional) run `phpcs --config-set warning_severity 3` to include warnings about return types, which I've marked with a lower priority until I move them into their own sniffs

Then enjoy!

You can find the accompanying zsh functions, `koolaid`, [here](https://github.com/xp-bar/.files/blob/master/.koolaid)
