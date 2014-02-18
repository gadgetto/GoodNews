<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * nocron.php can be used to reload mailpump periodically if Cron is not available
 *
 * @package goodnews
 */

sleep(30); // wait 30 seconds

// test: JavaScript method to reload mailpump.php script
$offset = is_numeric($_GET["offset"]) ? $_GET["offset"] : 0;
$max = 10;

if ($offset < $max)  {
    $offset++;
    ?>
    <script type="text/javascript">
        window.onload = function() {
            window.document.location.href = '<?php echo $_SERVER["PHP_SELF"]; ?>?offset=<?php echo $offset; ?>';
        }
    </script>
    <?php
    echo 'nocron.php Iteration: '.$offset;
    
    require_once('cron.php');
}
?>
