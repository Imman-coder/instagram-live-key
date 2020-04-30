<?php 

Class Alive
{
    public static $id = ''; 
    public static $user = '';
    public static $password = '';
    public static $response = [ 
        "id"=> "",
        "user"=> "", 
        "password"=> "",
        "require"=>false,
        "code"=> "",
        "server"=> '', 
        "key"=> '', 
        "status"=> [ ],
        "error"=> [ ]
    ];

    public static $url = "";

    public static function name ( $id = "" )
    {
        return ( empty ( self::$id ) ) ? "error.json" : self::$id.".json";
    }

    public static function login ( $ig ) 
    {
        self::$response [ "id" ] = self::$id;
        self::$response [ "user" ] = self::$user;
        self::$response [ "password" ] = self::$password;

        self::$url = 'keys/'.self::name ( self::$id ); 
        
        Archive::write ( self::$url, json_encode ( self::$response ) );
        
        try {
            
            $loginResponse =  $ig->login ( self::$user, self::$password );

            if ( $loginResponse !== null && $loginResponse->isTwoFactorRequired ( ) ) {
                
                self::$response [ "require" ] = true;
                
                Archive::write ( self::$url, json_encode ( self::$response ) );
                
                $twoFactorIdentifier = $loginResponse->getTwoFactorInfo ( )->getTwoFactorIdentifier ( );

                $verificationCode = "";

                $time_start = microtime ( true ); 

                $execution_time = 0;
                
                while( $execution_time < 60 ) {

                    $time_end = microtime ( true );

                    $execution_time = ( $time_end - $time_start ) / 60;

                    $res = json_decode ( Archive::read ( self::$url ), true );
                    
                    if ( !empty ( $res [ "code" ] ) ) {

                        self::$response [ "code" ] = $res [ "code" ];
                        $verificationCode = $res [ "code" ];

                        $execution_time = 100;

                        array_push ( self::$response [ "status" ] , array ( "code"=> self::$response [ "code" ] ) );
                    }
                    
                    sleep ( 1 );
                }

                $ig->finishTwoFactorLogin ( self::$user, self::$password, $twoFactorIdentifier, $verificationCode );

            }

            array_push ( self::$response [ "status" ] , array ( "acess_login"=> "OK" ) );

        } catch(\Exception $e) {

            array_push ( self::$response [ "error" ], array ( "Error_login"=> $e->getMessage ( ) ) );

        }

        Archive::write ( self::$url, json_encode ( self::$response ) );

    }

    //Block Responsible for Creating the Livestream.
    public static function broadcast($ig)
    {
        try {

            if ( count ( self::$response [ "error" ] ) > 0 ) {
                exit();
                return false;
                die ( "error" );
            }

            if (!$ig->isMaybeLoggedIn) { exit(); }

            $stream = $ig->live->create();
            
            $broadcastId = $stream->getBroadcastId();
            
            $ig->live->start($broadcastId);
        
            $streamUploadUrl = $stream->getUploadUrl();

            //Grab the stream url as well as the stream key.
            $split = preg_split("[".$broadcastId."]", $streamUploadUrl);

            $streamUrl = $split[0];
            $streamKey = $broadcastId.$split[1];

            self::$response [ "server" ] = $streamUrl;
            self::$response [ "key" ] = $streamKey;
            
            self::$url = 'keys/'.self::name ( self::$id );
            Archive::write ( self::$url, json_encode ( self::$response ) );

            self::newCommand($ig->live, $broadcastId, $streamUrl, $streamKey);
            
            $ig->live->getFinalViewerList($broadcastId);

            $ig->live->end($broadcastId); 


        } catch (\Exception $e) {
            array_push ( self::$response, array( 'Error_broadcast'=> $e->getMessage ( ) ) );
        }

    }

    public static function newCommand ( Live $live, $broadcastId, $streamUrl, $streamKey ) 
    {
        $handle = fopen ("php://stdin","r");
    
        $line = trim(fgets($handle));
    
        if($line == 'ecomments') {
            $live->enableComments($broadcastId);
    
        } elseif ($line == 'dcomments') {
            $live->disableComments($broadcastId);
    
        } elseif ($line == 'stop' || $line == 'end') {
            fclose($handle);
    
            //Needs this to retain, I guess?
            $live->getFinalViewerList($broadcastId);
            $live->end($broadcastId);
    
            $handle = fopen ("php://stdin","r");
            $archived = trim(fgets($handle));
    
            if ($archived == 'yes') {
                $live->addToPostLive($broadcastId);
            }
    
            exit();
    
        } elseif ($line == 'url') { } 
        elseif ($line == 'key') { } 
        elseif ($line == 'info') {
            
            $info = $live->getInfo($broadcastId);
            $status = $info->getStatus();
            $muted = var_export($info->is_Messages(), true);
            $count = $info->getViewerCount();
    
        } elseif ($line == 'viewers') {
    
            $live->getInfo($broadcastId);
    
            foreach ($live->getViewerList($broadcastId)->getUsers() as &$cuser) { }
    
        } elseif ($line == 'help') { } 
        else { }
    
        fclose($handle);
        
        self::newCommand($live, $broadcastId, $streamUrl, $streamKey);
        
    }
}