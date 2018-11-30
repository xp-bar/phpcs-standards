# PHPCS Standards - The XpBar edition

These are my personal phpcs standards, a collection of sniffs and rulesets from others, and some I've written myself (because, why not?).


![why not?](https://media0.giphy.com/media/zrOklTY2Hbl4s/giphy.gif?cid=3640f6095bf56b65483174474187a565)

## PREFACE

This repo contains both my own work, and the works of [Squiz Labs](https://github.com/squizlabs/PHP_CodeSniffer) and [Slevomat](https://github.com/slevomat/coding-standard), and uses phpcs Sniffs written by multiple other parties.

Repositories for sniffs not included in the default set provided by Squiz Labs:
- [slevomat/coding-standard](https://github.com/slevomat/coding-standard)
- [hostnet/phpcs-tool](https://github.com/hostnet/phpcs-tool)
- [sirbrillig/variable-analysis](https://github.com/sirbrillig/phpcs-variable-analysis)
- [xp-bar/phpcs-standards](#)

## INSTALLATION

### AUTOMATIC

*Before running the install or update scripts, make sure:*
1. you can clone git objects via the command line with `git clone`
2. `which phpcs` should return a path if you have previously installed `phpcs` (if you have not installed `phpcs` already, the install script will install it globally for you.)
3. you have a global installation of composer

All you should need to get started is the install script; simply

1. Clone the Repo
2. cd into the directory
3. (optional) `cat install.sh` -- check the source code, make sure it's safe!
4. `chmod +x ./install.sh`
5. run `./install`
6. (optional) run `./update` to update! (re-clones the repos and re-installs sniffs)

### MANUAL

*If the above doesn't work for you, or if you have `phpcs` installed but `which phpcs` returns a blank string, try the below.*
1. find your desired installation of phpcs. If you already have it installed, find where the bin file symlinks to - this is the install directory.
2. in the installation directory, find the `Standards` folder under `src` - for my global composer install, it's under `~/.composer/vendor/squizlabs/php_codesniffer/src/Standards`.
3. Clone each of repos listed in the preface above ([slevomat/coding-standard](https://github.com/slevomat/coding-standard), [hostnet/phpcs-tool](https://github.com/hostnet/phpcs-tool), [sirbrillig/variable-analysis](https://github.com/sirbrillig/phpcs-variable-analysis)) and find the folder that contains a folder called `Sniffs`. Unless the repos have changed since I've wrote this, this folder should be the top namespace for the project (ie. `SlevomatCodingStandard`, `Hostnet` and `VariableAnalysis`) and _should_ either be in the root of the project or under a src folder.
4. copy the folders containing `Sniffs` for each repo into the `squizlabs/php_codesniffer/src/Standards` folder (ie. copy `SlevomatCodingStandard`, `Hostnet` and `VariableAnalysis`).
5. finally, copy this repo's ruleset and sniffs (the entire `XpBar` folder) to `squizlabs/php_codesniffer/src/Standards`.

### POST INSTALLATION
1. update your editor config to point to your global phpcs installation - `which phpcs` and to use the `XpBar` standard as the default
2. (optional) run `phpcs --config-set warning_severity 3` to include warnings about return types, which I've marked with a lower priority until I move them into their own sniffs

You can find the accompanying zsh functions, `koolaid`, [here](https://github.com/xp-bar/.files/blob/master/.koolaid)
