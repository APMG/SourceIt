<?php

/****************************************
 * Carp for PHP, based on Carp for Perl
 * Note the class is called Carper because otherwise PHP
 * thinks carp() is a constructor.
 */

class Carper {

    /**
     *
     *
     * @param unknown $message
     */
    public static function carp($message) {

        $backtrace = debug_backtrace();
        if (isset($backtrace[2])) {
            $caller = $backtrace[2]['function'] . '()';
        }
        else {
            $caller = 'main()';
        }
        if (isset($backtrace[1])
            && isset($backtrace[1]['file'])
            && isset($backtrace[1]['line'])
        ) {
            $caller_line = $backtrace[1]['file'] . ' line ' . $backtrace[1]['line'];
        }
        else {
            $caller_line = '';
        }

        error_log('['. date('Y-m-d H:m:s')."] $message at $caller $caller_line" );

    }


    /**
     * Calls confess() and exit(1);
     *
     * @param unknown $message
     */
    public static function croak($message) {
        Carper::confess($message);
        exit(1);
    }


    /**
     * Prints full stacktrace to error_log().
     *
     * @param unknown $message (optional)
     */
    public static function confess($message=null) {
        if ($message) {
            Carper::carp($message);
        }
        error_log( var_dump( debug_backtrace(), true ) );
    }


}
