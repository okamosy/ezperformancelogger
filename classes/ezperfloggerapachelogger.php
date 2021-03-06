<?php
/**
 * The class implements methods allowing other code to parse perf-data from
 * Apache-formatted log files, and to create Apache-formatted logs.
 * NB: if you are looking for the code used to send perf. events to the Apache log,
 * it is not implemented here but in the eZPerfLogger class
 *
 * @author G. Giunta
 * @copyright (C) G. Giunta 2012-2013
 * @license Licensed under GNU General Public License v2.0. See file license.txt
 *
 * @todo !important rename this class since it does not implement eZPerfLoggerLogger
 */
class eZPerfLoggerApacheLogger implements eZPerfLoggerLogParser
{

    /// default parsing options
    static $options = array(
        'accepted_verbs' => array( 'GET', 'POST', 'PUT', 'HEAD' ), // exclude OPTIONS by default
        'excluded_uas' => array(), // arary of regexps to exclude user agents
        'excluded_ips' => array(), // arary of regexps to exclude source IP
    );

    /**
     * Parses a log line, expected to be in Apache "combined+" format:
     * - the line must begin with the combined format
     * - it is expected to contain N more values at the end correspoding to the $counters array
     * Expected log format: "%h %l %u %t \"%r\" % >s %b \"%{Referer}i\" \"%{User-Agent}i\" [CounterValue]*
     * @return mixed an array on success, false on failure, true if url matches an excluderegexp
     */
    static public function parseLogLine( $line, $counters = array(), $excludeRegexps = array() )
    {
        $countersCount = count( $counters );

        if ( !preg_match( '/([0-9.a-fA-F:]+) +([^ ]+) +([^ ]+) +\[([^]]+)\] +(.+)/', $line, $matches ) )
        {
            /// @todo log warning
            return false;
        }

        $time = strtotime( implode( ' ', explode( ':', str_replace( '/', '.', $matches[4] ), 2 ) ) );
        if ( !$time )
        {
            /// @todo log warning
            return false;
        }

        $ip = $matches[1];

        // filter on IP
        foreach( self::$options['excluded_ips'] as $regexp )
        {
            if ( preg_match( $regexp, $ip ) )
            {
                return true;
            }
        }

        $logPartArray = explode( '"', $matches[5] ); //preg_split( "/[\"]+/", $line );

        // there is no point in parsing this line further: we miss the perf-data part
        if ( count( $logPartArray ) < 4 && $countersCount )
        {
            return false;
        }

        // nb: generates a php warning when the url recorded by apache is too long.
        // In that case apache records a substring of the url in the access log, and here
        // we will find no protocol part
        list( $requireMethod, $url, $protocol ) = explode( ' ', $logPartArray[1] );

        // filter on HTTP verb
        /// @todo !important refactor to use isset() for speed
        if ( !in_array( $requireMethod, self::$options['accepted_verbs'] ) )
        {
            return true;
        }

        // filter on URL
        foreach( $excludeRegexps as $regexp )
        {
            if ( preg_match( $regexp, $url ) )
            {
                return true;
            }
        }

        // filter on User Agent
        // NB: Can only be done when UA is logged, which is not the default
        // (must use 'combined' format instead of 'common')
        foreach( self::$options['excluded_uas'] as $regexp )
        {
            if ( preg_match( $regexp, $logPartArray[5] ) )
            {
                return true;
            }
        }

        list( $respstatus, $respsize ) = explode( ' ', trim( $logPartArray[2], ' ' ) );

        if ( $countersCount )
        {
            /// NB: we assume there is no " in the 'perf counters' part
            $notePart = ltrim( rtrim( $logPartArray[count( $logPartArray )-1], " \n\r" ), ' ' );
            $notes = explode( ' ', $notePart );
            if ( count( $notes ) < $countersCount )
            {
                // could be any static resource
                return false;
            }
            else if ( count( $notes ) > $countersCount )
            {
                // The apache log might be set up to add extra stuff here, between the user agent's string and the perf logging data
                // so we just ignore it.
                // Note that this might also be a sign of a config error...
                $notes = array_slice( $notes, -1 * $countersCount );
            }

            $counters = array_combine( $counters, $notes );
        }
        else
        {
            $counters = null;
        }

        return array(
            'url' => $url,
            'time' => $time,
            'ip' => $ip,
            'response_status' => $respstatus,
            'response_size' => $respsize,
            'counters' => $counters );
    }

    /**
     * Returns a string corresponding to Apache log format (for current request)
     */
    static function apacheLogLine( $format = 'common', $size='-', $httpReturn = '200' )
    {
        switch ( $format )
        {
            /// LogFormat "%h %l %u %t \"%r\" % >s %b \"%{Referer}i\" \"%{User-Agent}i\"
            /// @todo add values for %l (remote logname), %u (remote user)
            case 'combined':
                return $_SERVER["REMOTE_ADDR"] . ' - - [' . date( 'd/M/Y:H:i:s O' ) . '] "' . $_SERVER["REQUEST_METHOD"] . ' ' . $_SERVER["REQUEST_URI"]. ' ' . $_SERVER["SERVER_PROTOCOL"] . '" 200 ' . $size . ' "' . @$_SERVER["HTTP_REFERER"] . '" "' . @$_SERVER["HTTP_USER_AGENT"] . '"';
            case 'common':
            default:
                return $_SERVER["REMOTE_ADDR"] . ' - - [' . date( 'd/M/Y:H:i:s O' ) . '] "' . $_SERVER["REQUEST_METHOD"] . ' ' . $_SERVER["REQUEST_URI"]. ' ' . $_SERVER["SERVER_PROTOCOL"] . '" 200 ' . $size;
        }
    }

    static public function setOptions( array $opts )
    {

    }
}

?>