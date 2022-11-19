<?php
/**
 * This config.core.sample.php includes the original config.core.php file
 * from the MODX installation based on this folder structure:
 *
 * /modx/  (<- the MODX root)
 * - - assets/
 * - - connectors/
 * - - core/
 * - - manager/
 * - - projects/  (<- your dev projects folder)
 * - - - - goodnews/
 * - - - - - - config.core.php  (<- we are here)
 * - - - - project2/
 * - - - - - - config.core.php
 * - - - - project3/
 * - - - - - - config.core.php
 * - - index.php
 * - - config.core.php  (<- the config.core.php from MODX)
 *
 * Be sure to modify this to your needs and rename this config.core.sample.php file
 * to config.core.php and to NOT include it in the git repo.
 */

include dirname(__FILE__, 3) . '/config.core.php';
