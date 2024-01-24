#!/usr/bin/env bash
#
# Set correct owner, group, permissions and SELinux contexts
# for all the project's folders and files.
#
USAGE="Usage: ${0}";

# Make sure only root can run our script
if [[ ${EUID} -ne 0 ]]; then
   echo "This script must be run as root." >&2;
   exit 1;
fi

# Prepare variables
DIR="$( realpath $( dirname ${0} )/../ )";
USERNAME="fwiep";
GROUPNAME="apache"
ONFEDORA="y";

# Detect OS we're running on; choose between Fedora (default) and RasPi OS
if [ ! -f "/etc/redhat-release" ]; then
  USERNAME="pi";
  GROUPNAME="www-data";
  ONFEDORA="n";
fi

# Set owner:group for all files and folders
sudo chown -R "${USERNAME}":"${GROUPNAME}" "${DIR}";

# Set permissions to 664 for files, 775 for folders
sudo find "${DIR}" -type f -print0 | sudo xargs -0 chmod 664
sudo find "${DIR}" -type d -print0 | sudo xargs -0 chmod 775

if [ "${ONFEDORA}" == "y" ]; then
  # Make all files and folders readable for Apache (httpd)
  sudo chcon --recursive -t httpd_sys_content_t "${DIR}";
fi

# ./scripts/*
sudo chmod 700 "${DIR}/scripts/"*;

# =====================
# | DL-FWieP specific |
# =====================

# ./data
sudo chcon --recursive -t httpd_sys_rw_content_t "${DIR}/data";
