<?php

/*
* This file is part of GeeksWeb Bot (GWB).
*
* GeeksWeb Bot (GWB) is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License version 3
* as published by the Free Software Foundation.
*
* GeeksWeb Bot (GWB) is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.  <http://www.gnu.org/licenses/>
*
* Author(s):
*
* © 2015 Kasra Madadipouya <kasra@madadipouya.com>
*
*/
require 'vendor/autoload.php';

$client = new Zelenin\Telegram\Bot\Api('191402453:AAFj4ww8v_YDa60iDi9Ck6iCMbX4izfUSLQ'); // Set your access token
$url = ''; // URL RSS feed
$update = json_decode(file_get_contents('php://input'));

//your app
try {

    if($update->message->text == 'ciao')
    {
      $my_user = $update->message->from->first_name;
      $response = $client->sendMessage([
        'chat_id' => $update->message->chat->id,
//        'text' => "Hi, what's up!?"
        'text' => "Hi, $my_user, what's up!?"
      ]);
    }

    if($update->message->text == '/answerme')
    {
    	$response = $client->sendChatAction(['chat_id' => $update->message->chat->id, 'action' => 'typing']);
    	$response = $client->sendMessage([
        	'chat_id' => $update->message->chat->id,
        	'text' => "I'll be happy to answer if you ask a question..."
     	]);
    }
    else if($update->message->text == '/help')
    {
    	$response = $client->sendChatAction(['chat_id' => $update->message->chat->id, 'action' => 'typing']);
    	$response = $client->sendMessage([
    		'chat_id' => $update->message->chat->id,
    		'text' => "List of commands :\n /answerme -> Provides an answer, any answer
    		/help -> Shows list of available commands"
    		]);

    }
    else if($update->message->text == '/case')
    {
      $response = $client->sendChatAction(['chat_id' => $update->message->chat->id, 'action' => 'typing']);
      $response = $client->sendMessage([
        'chat_id' => $update->message->chat->id,
        'text' => "I will create a Case for you"
        ]);

        //create a connection string from the PG database URL and then use it to connect
        $url=parse_url(getenv("DATABASE_URL"));
        $host = $url["host"];
        $port = $url["port"];
        $user = $url["user"];
        $password = $url["pass"];
        $dbname = substr($url["path"],1);
        $connect_string = "host='" . $host . "' ";
        $connect_string = $connect_string . "port=" . $port . " ";
        $connect_string = $connect_string . "user='" . $user . "' ";
        $connect_string = $connect_string . "password='" . $password . "' ";
        $connect_string = $connect_string . "dbname='" . $dbname . "' ";
        $db = pg_connect($connect_string);

        $query = "INSERT INTO Case (AccountId, Description, Priority, RecordTypeId, Status)
        VALUES ('0012400000eiYSb', 'A Support Case', 'Medium', '012240000002iSKAAY', 'New');";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());

        // free resultset
        pg_free_result($result);
        // close connection
        pg_close($db);

    }
    // else
    // {
    // 	$response = $client->sendChatAction(['chat_id' => $update->message->chat->id, 'action' => 'typing']);
    // 	$response = $client->sendMessage([
    // 		'chat_id' => $update->message->chat->id,
    // 		'text' => "Invalid command, please use /help to get list of available commands"
    // 		]);
    // }

} catch (\Zelenin\Telegram\Bot\NotOkException $e) {

    //echo error message ot log it
    //echo $e->getMessage();

}
