# fast-forward

[![Join the chat at https://gitter.im/phparsenal/fast-forward](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/phparsenal/fast-forward?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

[![Build Status](https://travis-ci.org/phparsenal/fast-forward.svg?branch=master)](https://travis-ci.org/phparsenal/fast-forward) [![Dependency Status](https://www.versioneye.com/user/projects/558dbe19316338002400001c/badge.svg?style=flat)](https://www.versioneye.com/user/projects/558dbe19316338002400001c)

**fast-forward** lets you remember, find and open your most used commands and folders.

* [fast-forward](#fast-forward)
    * [Setup](#setup)
        * [Windows](#windows)
        * [Linux](#linux)
        * [Mac](#mac)
    * [Usage](#usage)
    * [Settings](#settings)
        * [Supported and default settings](#supported-and-default-settings)
        * [Using custom settings in commands](#using-custom-settings-in-commands)

## Setup

### Windows
1. Download and extract https://github.com/phparsenal/fast-forward/archive/master.zip
2. Install composer using the [Windows installer](https://getcomposer.org/Composer-Setup.exe)
3. Make sure dependencies are up to date:

        composer install

4. Edit the file `ff.bat` and change `ffpath` to the folder you put fast-forward in.
5. Copy `ff.bat` to a global path so that it is always available on the command line.

### Linux

1. Download the project:

        cd ~
        git clone https://github.com/phparsenal/fast-forward.git

2. [Install composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx):

        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer

3. Make sure dependencies are up to date:

        composer install

4. Afterwards make the `ff` command available globally by adding this to your `~/.bashrc` or `~/.bash_aliases`:

        alias ff='. /path/to/fast-forward/ff.sh'

### Mac
n/a

## Usage
Add a new command in one line:  

    ff add [-c cmd] [-d desc] [-s shortcut]

List all available commands and execute the selection:

    ff

Searching for _htd*_
If the only result is _htd_ it will be executed, otherwise all matches will be displayed first.

    ff htd

## Settings

```
Usage: ff [-i file, --import file] [-l, --list] [set] [key] [value]

Optional Arguments:
	-i file, --import file
		Import from the specified file
	-l, --list
		Show a list of all current settings. Save to file: ff set -l > file.txt
```
e.g.

`ff set ff.limit 20` Limit to 20 results  
`ff set -l > settings.txt` Dump settings  
`ff set < settings.txt` or `ff set -i settings.txt` Import settings

### Supported and default settings
The following settings are supported by fast-forward:

* **ff.limit**
    * Limit amount of results (> 0 or 0 for no limit)
    * Default: 0
* **ff.sort**
    * Sort order of results (shortcut, description, command, hit_count, ts_created, ts_modified)
    * Default: hit_count
* **ff.interactive**
    * Ask for missing input interactively (0 never, 1 always)
    * Default: 1
* **ff.color**
    * Enable color output on supported systems (0/1)
    * Default: 1

### Using custom settings in commands
You can also create your own settings which can be accessed in commands:  
`ff set location tokio`

Use the setting name surrounded by `@` in your commands:  
`weather @location@`

The identifiers are replaced with the current or default value of the setting:  
`weather tokio`