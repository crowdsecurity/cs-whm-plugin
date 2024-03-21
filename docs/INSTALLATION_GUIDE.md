![CrowdSec Logo](images/logo_crowdsec.png)

# CrowdSec WHM plugin

## Installation Guide


<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**

- [Requirements](#requirements)
- [Installation](#installation)
  - [Retrieve sources](#retrieve-sources)
    - [Download a specific release archive (option 1)](#download-a-specific-release-archive-option-1)
    - [Clone the repository (option 2)](#clone-the-repository-option-2)
  - [Install the plugin](#install-the-plugin)
  - [Go back to your WHM dashboard](#go-back-to-your-whm-dashboard)
- [Uninstallation](#uninstallation)
- [Upgrade](#upgrade)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->


## Requirements

- PHP >= 7.2.5
- CPanel+WHM installed (CPanel version > 66)


## Installation

### Retrieve sources

First, connect to your WHM server.
Go to your home directory or in any directory that can be used to download the sources.

Then, you can download an archive of the sources (recommended) or clone the repository.

#### Download a specific release archive (option 1)

* Run the following command:

```shell
wget https://github.com/crowdsecurity/cs-whm-plugin/archive/refs/tags/v0.0.9.tar.gz
```

* Extract sources:

```shell
tar -xvf v0.0.9.tar.gz
``` 

* Go to the extracted folder:

```shell
cd cs-whm-plugin-0.0.9/plugin
``` 


#### Clone the repository (option 2)

* Run the following command:

```shell
git clone git@github.com:crowdsecurity/cs-whm-plugin.git
```
```shell
cd cs-whm-plugin/plugin
```


### Install the plugin

Once you've retrieved the sources, you can install the plugin by running the `install` script as root:

```shell
sudo sh crowdsec.sh install
```
    
You should see:

```
Installing CrowdSec plugin...
crowdsec registered
```

If you've already installed CrowdSec, the script will also use the `cscli` command to install the WHM collection, create a few acquisition files and restart the CrowdSec service.

If you don't want the script to install the WHM collection, you can use the `--only-plugin` option:

```
sudo sh crowdsec.sh install --only-plugin
```


```shell

### Go back to your WHM dashboard


CrowdSec should appear in the sidebar (in the bottom Plugins sections)


## Uninstallation

To remove the plugin, run the `uninstall` script as root:


```sudo sh crowdsec.sh uninstall```
    
You should see:

```
Uninstalling CrowdSec plugin...
crowdsec unregistered
```

## Upgrade

When a new version of the plugin is available, retrieve the new sources: 
- download and decompress new release archive if you've chosen [option 1](#download-a-specific-release-archive-option-1).
- `git pull` from the cloned repository if you've chosen [option 2](#clone-the-repository-option-2).

Then, run the `reinstall` script as root

* ```sudo sh crowdsec.sh reinstall```

You should see:

```
Reinstalling CrowdSec plugin...
crowdsec unregistered
crowdsec registered
```
