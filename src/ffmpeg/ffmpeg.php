<?php

$ffmpegpath = realpath("../../ffmpeg.exe");

$input = 'teaser.flv';
$output = 'teaser.jpg';

if (make_jpg($input, $output)){
    echo 'success';
}else{
    echo 'bah!';

    var_dump($ffmpegpath);
}

function make_jpg($input, $output, $fromdurasec="23") {
    global $ffmpegpath;

    if(!file_exists($input)) return false;
    $command = "$ffmpegpath -i $input -an -ss 00:00:$fromdurasec -r 1 -vframes 1 -f mjpeg -y $output";

    @exec( $command, $ret );
    if(!file_exists($output)) return false;
    if(filesize($output)==0) return false;
    return true;
}