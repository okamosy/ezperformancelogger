<?php
class eZperformanceloggerInfo
{
    static function info()
    {
        return array( 'Name' => "<a href=\"http://projects.ez.no/ezperformancelogger\">ezperformancelogger</a>",
                      'Version' => "0.9-dev",
                      'Copyright' => "Copyright (C) 2010-2012 Gaetano Giunta",
                      'License' => "GNU General Public License v2.0",
                      '3rdparty_software' =>
                            array ( 'name' => 'XHProf',
                                    'Version' => '0.9.2',
                                    'copyright' => 'Facebook, Inc.',
                                    'license' => 'Apache License, Version 2.0',
                                    'info_url' => 'http://pecl.php.net/package/xhprof' )
                     );
    }
}
?>