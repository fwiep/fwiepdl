<?php
/**
 * Localization setup
 * 
 * Sources:
 * - https://www.toptal.com/php/build-multilingual-app-with-gettext
 * - https://blog.udemy.com/php-gettext/
 *
 * PHP version 8
 *
 * @category Configuration
 * @package  DL-FWieP
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
if (!defined('_FWIEPEXEC')) {
    http_response_code(400);
    die('No direct access!');
}
/**
 * Global variable
 * 
 * @var array All supported languages for the UI of the app
 */
$supportedLangs = [
    'en' => 'English',
    'nl' => 'Nederlands',
    'de' => 'Deutsch'
];

/**
 * Verifies if the given $locale is supported in the project
 * 
 * @param string $locale the locale to check
 * 
 * @return bool
 */
function valid(?string $locale = null) : bool
{
    global $supportedLangs;
    return in_array($locale, array_keys($supportedLangs));
}

$lang = filter_input(INPUT_GET, 'lang', FILTER_DEFAULT);

if (empty($lang) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    
    // default: look for the languages the browser says the user accepts
    $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    array_walk(
        $langs, function (&$lang) {
            $lang = strtr(strtok($lang, ';'), ['-' => '_']);
        }
    );
    foreach ($langs as $browser_lang) {
        if (valid($browser_lang)) {
            $lang = $browser_lang;
            break;
        }
    }
}
$lang = valid($lang) ? $lang : 'en';

// this might be useful for date functions (LC_TIME) or money
// formatting (LC_MONETARY), for instance
switch($lang) {

default:
case 'en':
    $l = setlocale(LC_ALL, ['en_US.utf8', 'en_US', 'en', 'english', 'eng']);
    break;
case 'nl':
    $l = setlocale(LC_ALL, ['nl_NL.utf8', 'nl_NL', 'nl', 'dutch', 'nld']);
    break;
case 'de':
    $l = setlocale(LC_ALL, ['de_DE.utf8', 'de_DE', 'de', 'german', 'ger']);
    break;
}
// here we define the global system locale given the found language
putenv("LANG=$l");

// this will make Gettext look for ./locale/<lang>/LC_MESSAGES/main.mo
bindtextdomain('main', './locale');

// indicates in what encoding the file should be read
bind_textdomain_codeset('main', 'UTF-8');

// here we indicate the default domain the gettext() calls will respond to
textdomain('main');
