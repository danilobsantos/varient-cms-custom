<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code


/*
|--------------------------------------------------------------------------
| CUSTOM CONSTANTS
|--------------------------------------------------------------------------
| Centralized application-wide constants for performance, UI limits,
| cache settings, and system defaults.
*/

/*
|--------------------------------------------------------------------------
| Application Version
|--------------------------------------------------------------------------
| Current application version number.
| Update this value with each major or minor release.
*/
defined('VARIENT_VERSION')                          || define('VARIENT_VERSION', '3.0');

/*
|--------------------------------------------------------------------------
| Showcase Content Limits
|--------------------------------------------------------------------------
| Maximum number of posts stored in showcase-related sections
| such as sliders, featured posts, editors picks, and trending blocks.
| Helps prevent unnecessary database growth and keeps queries optimized.
*/
defined('LIMIT_SHOWCASE_POSTS') || define('LIMIT_SHOWCASE_POSTS', 50);

/*
|--------------------------------------------------------------------------
| Sidebar Tags Limit
|--------------------------------------------------------------------------
| Maximum number of tags displayed in the sidebar tags widget.
| Keeps sidebar layouts clean and prevents overcrowding.
*/
defined('SIDEBAR_TAGS_LIMIT')           || define('SIDEBAR_TAGS_LIMIT', 20);

/*
|--------------------------------------------------------------------------
| Browser Cache Settings
|--------------------------------------------------------------------------
| Global toggle for frontend SessionStorage-based browser caching.
| Disable during development/debugging to bypass cached assets/data.
| Version key can be updated to instantly invalidate old cache.
*/
defined('BROWSER_CACHE')                || define('BROWSER_CACHE', true);
defined('BROWSER_CACHE_VERSION_KEY')    || define('BROWSER_CACHE_VERSION_KEY', 'vr_browser_cache_version');

/*
|--------------------------------------------------------------------------
| Sitemap Refresh Interval
|--------------------------------------------------------------------------
| Defines how often the sitemap cache should refresh, in seconds.
| Helps search engines receive updated content efficiently.
*/
defined('SITEMAP_REFRESH_INTERVAL')     || define('SITEMAP_REFRESH_INTERVAL', 3600); // 1 hour

/*
|--------------------------------------------------------------------------
| Default Media Assets
|--------------------------------------------------------------------------
| Default fallback assets used when no custom media is assigned.
*/
defined('AVATAR_GUEST')                 || define('AVATAR_GUEST', 'assets/media/user.png');

/*
|--------------------------------------------------------------------------
| Comments System
|--------------------------------------------------------------------------
| Number of comments loaded per request or pagination batch.
*/
defined('COMMENT_LIMIT')                || define('COMMENT_LIMIT', 6);

/*
|--------------------------------------------------------------------------
| Post Tags
|--------------------------------------------------------------------------
| Maximum number of tags allowed or displayed per post.
*/
defined('POST_TAGS_LIMIT')              || define('POST_TAGS_LIMIT', 20);

/*
|--------------------------------------------------------------------------
| Post Display Limits
|--------------------------------------------------------------------------
| Character limits for frontend title and summary rendering.
| Used to maintain consistent card, widget, and listing layouts.
*/
defined('POST_DISPLAY_TITLE_LIMIT')     || define('POST_DISPLAY_TITLE_LIMIT', 55); //55 characters
defined('POST_DISPLAY_SUMMARY_LIMIT')   || define('POST_DISPLAY_SUMMARY_LIMIT', 80); //80 characters

/*
|--------------------------------------------------------------------------
| Application Cache
|--------------------------------------------------------------------------
| Global server-side cache lifetime in seconds.
| Used for reusable application data and query caching.
*/
defined('APP_CACHE_TTL')                || define('APP_CACHE_TTL', 86400); // 24 Hours

/*
|--------------------------------------------------------------------------
| File Manager
|--------------------------------------------------------------------------
| Number of media/files displayed per page in admin file manager.
| Higher values improve bulk management but may affect performance.
*/
defined('FILE_MANAGER_PER_PAGE')        || define('FILE_MANAGER_PER_PAGE', 120);