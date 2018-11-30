# PHPCS Standards - The XpBar edition

These are my personal phpcs standards, a collection of sniffs and rulesets from others, and some I've written myself.

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

=======

## SLEVOMAT AND REPO LICENSE

The MIT License (MIT) Copyright (c) 2018 Nicholas Ireland, 2015 Slevomat.cz, s.r.o.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## PHPCS LICENSE

PHPCS and related libraries Copyright (c) 2012, Squiz Pty Ltd (ABN 77 084 670 600)
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
* Neither the name of Squiz Pty Ltd nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

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
