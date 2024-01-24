#!/usr/bin/env bash
#
# Determine the MD5- and SHA-384 hashes of included files, then
# define them as global PHP constants

DIR="$( realpath $( dirname ${0} )/../ )";

# admin.min.js
MD5_ADMIN_JS="$( md5sum ${DIR}/js/admin.min.js | cut -s -d ' ' -f 1 )";
sed -i -e "s!define('MD5_ADMIN_JS', '[^']*');!define('MD5_ADMIN_JS', '${MD5_ADMIN_JS}');!" "${DIR}/constants.php";
SHA_ADMIN_JS="$( openssl dgst -sha384 -binary ${DIR}/js/admin.min.js | openssl base64 -A )";
sed -i -e "s!define('SHA_ADMIN_JS', '[^']*');!define('SHA_ADMIN_JS', 'sha384-${SHA_ADMIN_JS}');!" "${DIR}/constants.php";

# admin.min.css
MD5_ADMIN_CSS="$( md5sum ${DIR}/css/admin.min.css | cut -s -d ' ' -f 1 )";
sed -i -e "s!define('MD5_ADMIN_CSS', '[^']*');!define('MD5_ADMIN_CSS', '${MD5_ADMIN_CSS}');!" "${DIR}/constants.php";
SHA_ADMIN_CSS="$( openssl dgst -sha384 -binary ${DIR}/css/admin.min.css | openssl base64 -A )";
sed -i -e "s!define('SHA_ADMIN_CSS', '[^']*');!define('SHA_ADMIN_CSS', 'sha384-${SHA_ADMIN_CSS}');!" "${DIR}/constants.php";

# client.min.js
MD5_CLIENT_JS="$( md5sum ${DIR}/js/client.min.js | cut -s -d ' ' -f 1 )";
sed -i -e "s!define('MD5_CLIENT_JS', '[^']*');!define('MD5_CLIENT_JS', '${MD5_CLIENT_JS}');!" "${DIR}/constants.php";
SHA_CLIENT_JS="$( openssl dgst -sha384 -binary ${DIR}/js/client.min.js | openssl base64 -A )";
sed -i -e "s!define('SHA_CLIENT_JS', '[^']*');!define('SHA_CLIENT_JS', 'sha384-${SHA_CLIENT_JS}');!" "${DIR}/constants.php";

# client.min.css
MD5_CLIENT_CSS="$( md5sum ${DIR}/css/client.min.css | cut -s -d ' ' -f 1 )";
sed -i -e "s!define('MD5_CLIENT_CSS', '[^']*');!define('MD5_CLIENT_CSS', '${MD5_CLIENT_CSS}');!" "${DIR}/constants.php";
SHA_CLIENT_CSS="$( openssl dgst -sha384 -binary ${DIR}/css/client.min.css | openssl base64 -A )";
sed -i -e "s!define('SHA_CLIENT_CSS', '[^']*');!define('SHA_CLIENT_CSS', 'sha384-${SHA_CLIENT_CSS}');!" "${DIR}/constants.php";

# Copy font-awesome fonts out of ./vendor
cp -f "${DIR}/vendor/fortawesome/font-awesome/webfonts/"* "${DIR}/fonts";
