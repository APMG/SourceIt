<?php

/*****************************************************
 */


/**
 * Maximum value of $len is 24. Default is 12.
 *
 * @param int     $len (optional)
 * @return unknown
 */


function IFDB_generate_uuid($len=12) {
    $max = mt_getrandmax();
    $t_val = sprintf('%04x%04x%04x%04x%04x%04x',
        mt_rand( 0, $max ),
        mt_rand( 0, $max ),
        mt_rand( 0, $max ),
        mt_rand( 0, $max ),
        mt_rand( 0, $max ),
        mt_rand( 0, $max )
    );
    return substr( $t_val, 0, $len );
}



?>
