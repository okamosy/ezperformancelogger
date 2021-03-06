<?php
/**
 * @author G. Giunta
 * @copyright (C) G. Giunta 2013
 * @license Licensed under GNU General Public License v2.0. See file license.txt
 */
class eZPerfLoggerRandomFilter implements eZPerfLoggerFilter
{
    public static function shouldLog( array $data, $output )
    {
        $ini = eZINI::instance( 'ezperformancelogger.ini' );
        if ( rand( 0, $ini->variable( 'LogFilterSettings', 'MemoryhungrypagesFilter' ) ) <= 1 )
        {
            return true;
        }
        return false;
    }
}
