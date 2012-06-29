<?php
/**************************************************************************
 *
 *   Copyright 2010 American Public Media Group
 *
 *   This file is part of AIR2.
 *
 *   AIR2 is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   AIR2 is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with AIR2.  If not, see <http://www.gnu.org/licenses/>.
 *
 *************************************************************************/

class Stopwatch {

    private $start_time;
    private $last_click;

    /**
     *
     */


    public function __construct() {
        $this->start_time = microtime(true);
        $this->last_click = $this->start_time;
    }


    /**
     *
     *
     * @param unknown $msg (optional)
     * @return unknown
     */
    public function click($msg=null) {
        $now = microtime(true);
        $elapsed = sprintf("%0.4f", $now - $this->last_click);
        $this->last_click = $now;
        if (isset($msg)) {
            return sprintf("%s elapsed %s", $msg, $elapsed);
        }
        return $elapsed;
    }


    /**
     *
     *
     * @param unknown $msg
     */
    public function print_click($msg) {
        print "<!-- " . $this->click($msg) . " -->\n";
    }


}


?>
