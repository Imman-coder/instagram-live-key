<?php

Class Archive {

    public static $url = '';
    
    public static function write($content ) 
    {
        $fileStream = fopen(Archive::$url,'w');
        fwrite($fileStream, $content);
        fclose($fileStream);
        $fileStream = null;
    }

}