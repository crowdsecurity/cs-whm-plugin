#!/bin/bash
# Crowdsec - Crowdsec is a free, modern & collaborative behavior detection engine, coupled with a global IP reputation network.
# https://crowdsec.net/

# Flag to determine if only the plugin should be installed
ONLY_PLUGIN=0

check_for_only_plugin_argument() {
    for arg in "$@"; do
        if [ "$arg" = "--only-plugin" ]; then
            ONLY_PLUGIN=1
            return
        fi
    done
}



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
  /usr/local/cpanel/bin/register_appconfig ./crowdsec.conf
    
  # Copy plugin files to their locations and update permissions.
  /bin/cp -R ./endpoints /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/cp -R ./assets /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/cp -R ./src /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/cp -R ./vendor /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  chmod -R 755 /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec/*

  /bin/cp ./cs_48.png /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec
  chmod 755 /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec/cs_48.png

  # Install crowdsec/whm collection
  if [ $ONLY_PLUGIN -ne 1 ]; then
    install_collection
  fi
}

uninstall () {
  # Unregister the plugin with AppConfig.
  /usr/local/cpanel/bin/unregister_appconfig ./crowdsec.conf
  
  # Remove plugin files.
  /bin/rm -rf /usr/local/cpanel/whostmgr/docroot/cgi/crowdsec
  /bin/rm -rf /usr/local/cpanel/whostmgr/docroot/addon_plugins/crowdsec
}

add_acquisition_files () {
  if [ -d /etc/crowdsec ]
    dest=/etc/crowdsec/acquis.d
    src=./src/Acquisition/examples
    then
      if [ ! -d "$dest" ]
        then
          mkdir "$dest"
          chmod -R 755 "$dest"
      fi
      for file in "$src"/*; do
        # Extract filename
        filename=$(basename "$file")
        # Check if the file does not exist in the destination directory
        if [ ! -f "$dest/$filename" ]; then
          echo "Copying $filename to $dest"
          /bin/cp "$file" "$dest"
          chmod 644 "$dest/$filename"
        fi
      done
  else
    echo "/etc/crowdsec directory not found."
  fi
}

install_collection () {
  if command -v cscli >/dev/null 2>&1; then
    echo "cscli found, installing crowdsecurity/whm collection..."
    cscli hub update
    cscli --error collections install crowdsecurity/whm
    add_acquisition_files
    echo "Restarting crowdsec service..."
    systemctl restart crowdsec
  else
    echo "CrowdSec cscli command not found."
  fi
}

check_for_only_plugin_argument "$@"

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
