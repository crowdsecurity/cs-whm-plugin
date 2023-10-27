![CrowdSec Logo](images/logo_crowdsec.png)

# CrowdSec WHM plugin

## Installation Guide


<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->


<!-- END doctoc generated TOC please keep comment here to allow auto update -->


## Requirements

- PHP >= 7.2.5
- CPanel+WHM installed (CPanel version > 66)


## Installation

### Retrieve sources

First, connect to your WHM server and go to your home directory.

Then, you can download an archive of the sources (recommended) or clone the repository.

#### Download a specific release archive

* Run the following command:
    * ```wget https://github.com/crowdsecurity/cs-whm-plugin/archive/refs/tags/v0.0.1.tar.gz```
* Extract sources:
    * ```tar -xvf v0.0.1.tar.gz``` 
* Go to the extracted folder:
    * ```cd cs-whm-plugin-0.0.1``` 


#### Clone the repository

* Run the following command:
    * ```git clone git@github.com:crowdsecurity/cs-whm-plugin.git```
    * ```cs-whm-plugin```


### Install the plugin

Once you retrieved the sources, you can install the plugin.
      
* Run the `install` script as root
    * ```sudo sh crowdsec.sh install```
    * You should see:
        * Installing CrowdSec plugin...
        * crowdsec registered


### Go back to your WHM dashboard


CrowdSec should appear in the sidebar (in the bottom Plugins sections)


## Uninstallation

To remove the plugin: 

* Run the `uninstall` script as root
    * ```sudo sh crowdsec.sh uninstall```
    * You should see:
        * Removing CrowdSec plugin...
        * crowdsec unregistered
