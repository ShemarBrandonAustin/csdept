<?php

class StringUtilities {
    
    // strip UTF-8 BOM prefix (if any) from a given string
    // [NOTE: In the calling chain, if there are PHP script files that have UTF-8 BOMs, then the produced 
    //  PHP UTF-8 output may end up having one or more UTF8 BOM prefixes, e.g., when retrieved via an 
    //  HTTP wrapper in a "standalone" API-style call such as 'file_get_contents( <http_url> )']
    public function stripUTF8BOMPrefix( $aString ) {
        $utf8BOM = chr( 239 ) . chr( 187 ) . chr( 191 );  // 0xEFBBBF
        $utf8BOMLen = 3;
        $workString = $aString;  // Default
        $workStringLen = strlen( $workString );
        
        // check for boundary case(s)
        if ( $workStringLen < $utf8BOMLen ) {
            return $workString;  // I.e., the default value as set up ^ (the specified string itself)
        }
        
        // determine whether the specified string has such a prefix
        $pos = strpos( $workString, $utf8BOM );
        
        // For Debug ONLY!!!
        //echo "\$pos = " . ( ( $pos === false ) ? "false" : $pos ) . "<br/><br/>";
        
        // check whether the specified string indeed has such a prefix
        while ( ( $pos !== false ) && ( $pos == 0 ) ) {
            // strip it off
            if ( $workStringLen > $utf8BOMLen ) {
                $workString = substr( $workString, $utf8BOMLen );
                $workStringLen -= $utf8BOMLen;
                
                // For Debug ONLY!!!
                //echo "\$workString = " . "(" . $workStringLen . ") " . "'" . $workString . "'" . "<br/><br/>";
                
            } else {
                $workString = "";
                $workStringLen = 0;
            }
            
            // (re-)determine whether the specified string (still) has such a prefix
            $pos = strpos( $workString, $utf8BOM );
        }
        
        return $workString;
    }
}  // class StringUtilities
?>
