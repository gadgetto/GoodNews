<?php
/**
 * This sample config.core.php assumes a directory structure like this:
 *
 * - modx/
 * - - assets/
 * - - core/
 * - - connectors/
 * - - manager/
 * - - config.core.php
 * - - index.php
 * - - - modxdev/
 * - - - - project1/
 * - - - - - config.core.php
 * - - - - project2/
 * - - - - - config.core.php
 *
 * where modx/ is the MODX install used for developing various packages.
 * The config.core.php is set up to include the original modx/config.core.php file. The
 * various independent packages can then have their own config.core.php files with the contents
 * as shown below.
 *
 * Be sure to rename this config.core.sample.php file to config.core.php and to NOT include
 * it in the git repo.
 */

include direname(dirname(dirname(__FILE__))) . '/config.core.php';
