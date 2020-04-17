<?php 

Class Alive
{
    public static $user = '';
    public static $password = '';
    public static $id = ''; 
    public static $response = [ "server"=> '', "key"=> '', "error"=> []];
    

    public static function login($ig) 
    {
        Archive::$url = 'keys/'.Alive::$id.'.stream.json'; 
        Archive::write(json_encode(Alive::$response));

        try {

            $loginResponse =  $ig->login(Alive::$user, Alive::$password);
            
            /*if ($loginResponse !== null && $loginResponse->isTwoFactorRequired()) {
                $twoFactorIdentifier = $loginResponse->getTwoFactorInfo()->getTwoFactorIdentifier();
                $verificationCode = readline("Code: ");
                $ig->finishTwoFactorLogin(IG_USERNAME, IG_PASS, $twoFactorIdentifier, $verificationCode);
            }*/

        } catch(\Exception $e) {
            array_push(Alive::$response["error"], array("Error_login"=>$e->getMessage()));
        }
    }

    //Block Responsible for Creating the Livestream.
    public static function broadcast($ig)
    {
        try {

            if (!$ig->isMaybeLoggedIn) { exit(); }

            $stream = $ig->live->create();
            
            $broadcastId = $stream->getBroadcastId();
            
            $ig->live->start($broadcastId);
        
            $streamUploadUrl = $stream->getUploadUrl();

            //Grab the stream url as well as the stream key.
            $split = preg_split("[".$broadcastId."]", $streamUploadUrl);

            $streamUrl = $split[0];
            $streamKey = $broadcastId.$split[1];

            Alive::$response["server"] = $streamUrl;
            Alive::$response["key"] = $streamKey;
            
            Archive::$url = 'keys/'.Alive::$id.'.stream.json'; 
            Archive::write(json_encode(Alive::$response));

            Alive::newCommand($ig->live, $broadcastId, $streamUrl, $streamKey);
            
            $ig->live->getFinalViewerList($broadcastId);
            $ig->live->end($broadcastId); 


        } catch (\Exception $e) {
            array_push(Alive::$response, array( 'Error_broadcast'=> $e->getMessage()));
        }

    }

    public static function newCommand(Live $live, $broadcastId, $streamUrl, $streamKey) 
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
        
        
        Alive::newCommand($live, $broadcastId, $streamUrl, $streamKey);
        
    }
}