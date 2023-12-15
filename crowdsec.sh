#!/bin/bash
# Crowdsec - Crowdsec is a free, modern & collaborative behavior detection engine, coupled with a global IP reputation network.
# https://crowdsec.net/

install () {
  # Check for and create the directory for plugin and AppConfig files.
  if [ ! -d /var/cpanel/apps ]
      then
      mkdir /var/cpanel/apps
      chmod 755 /var/cpanel/apps
  fi
  
  # Check for and create the directory for plugin CGI/PHP files.
  if [ ! -d /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec ]
    then
      mkdir /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
      chmod 755 /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  fi

   # Check for and create the directory for plugin addon_plugins files.
  if [ ! -d /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec ]
    then
      mkdir -p /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec
      chmod -R 755 /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec
  fi
  

  # Register the plugin with AppConfig.
  /usr/local/cpanel/bin/register_appconfig ./plugin/crowdsec.conf
    
  # Copy plugin files to their locations and update permissions.
  /bin/cp -R ./plugin/endpoints /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/cp -R ./plugin/assets /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/cp -R ./plugin/src /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/cp -R ./plugin/vendor /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  chmod -R 755 /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec/*

  /bin/cp ./plugin/cs_48.png /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec
  chmod 755 /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec/cs_48.png
}

uninstall () {
  # Unregister the plugin with AppConfig.
  /usr/local/cpanel/bin/unregister_appconfig ./plugin/crowdsec.conf
  
  # Remove plugin files.
  /bin/rm -rf /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/rm -rf /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec
}

case $1 in
  install)
    echo "Installing CrowdSec plugin..."
    install
    ;;
  uninstall|remove)
    echo "Uninstalling CrowdSec plugin..."
    uninstall
    ;;
  reinstall)
    echo "Reinstalling CrowdSec plugin..."
    uninstall && install
    ;;
  *)
    echo "Usage: $0 {install|uninstall|reinstall}"
    exit 1
    ;;
esac
