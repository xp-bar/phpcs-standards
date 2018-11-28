# PHPCS Standards - The XpBar edition

These are my personal phpcs standards, a collection of sniffs and rulesets from others, and some I've written myself.

*Before Starting, make sure you can:*
1. clone git objects via the command line with `git clone`
2. `which phpcs` should return a path

All you should need to get started is the install script; simply

1. Clone the Repo
2. cd into the directory
3. (optional) `cat install.sh` -- check the source code, make sure it's safe!
4. `chmod +x ./install.sh`
5. run `./install`
6. (optional) run `./update` to update! (re-clones the repos and re-installs sniffs)
7. update your editor config to point to your global phpcs installation - `which phpcs` and to use the `XpBar` standard as the default

Then enjoy!

You can find the accompanying zsh functions, `koolaid`, [here](https://github.com/xp-bar/.files/blob/master/.koolaid)

=======

### License

PHPCS and related libraries Copyright (c) 2012, Squiz Pty Ltd (ABN 77 084 670 600) and Nicholas Ireland
This repo Copyright (c) 2018 Nicholas Ireland
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
* Neither the name of Nicholas Ireland, Squiz Pty Ltd nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
