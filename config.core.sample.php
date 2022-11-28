<?php
/**
 * This config.core.sample.php includes the original config.core.php file
 * from your MODX installation based on this folder structure:
 *
 * https://your.domain.dev/
 * modx/  (<- the MODX root)
 * - - assets/
 * - - connectors/
 * - - core/
 * - - manager/
 * - - config.core.php  (<- the config.core.php from MODX)
 * - - index.php
 * projects/  (<- your dev projects folder)
 * - - goodnews/
 * - - - - config.core.php  (<- we are here)
 * - - project2/
 * - - - - config.core.php
 * - - project3/
 * - - - - config.core.php
 *
 * PLEASE NOTE:
 * 
 * To use this project structure (subfolder MODX installation and development outside of MODX root)   
 * you need to change the following "session_" system settings in all MODX installations:
 * 
 * MODX 2 install sample:
 *   session_name = EXTRAS2x  (<- different session name in all installations)
 *   session_cookie_path = /  (<- needs to be same in all installations)
 * 
 * MODX 3 install sample:
 *   session_name = EXTRAS3x
 *   session_cookie_path = /
 * 
 * Be sure to modify this to your needs and rename this config.core.sample.php file
 * to config.core.php and to NOT include it in the git repo.
 */

include dirname(__DIR__, 2) . '/modx/config.core.php';
