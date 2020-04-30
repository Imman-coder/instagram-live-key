<?php

$post = json_decode ( file_get_contents ( "php://input" ), true );

if ( !empty ( $post ) && $post [ "action" ] == "stream" && !empty ( $post [ "id" ] ) ) {
    
    define ( 'ID',          $post [ "id" ] );
    define ( 'IG_USERNAME', $post [ "user" ] );
    define ( 'IG_PASS',     $post [ "password" ] );
    define ( 'SERVER',      "" );
    define ( 'KEY',         "" );

    require_once ( 'go.php' );
}
