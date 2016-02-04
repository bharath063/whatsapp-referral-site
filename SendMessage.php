<?php
require_once 'WhatsAPI/src/whatsprot.class.php';
require_once 'wa_config.php';

$wa_debug = false;

// Create a instance of WhastPort.
$wa_handler = new WhatsProt($wa_username, $wa_nickname, $wa_debug);

$wa_handler->connect(); // Connect to WhatsApp network
$wa_handler->loginWithPassword($wa_password); // logging in with the password we got!



function sendMessageViaWhatsapp($to, $message, $secret){
    global $wa_handler;
    if($secret=="mysecret"){
          $wa_handler->sendMessage($to , $message);      
    sleep(1);

    }
        
     
}




?>
