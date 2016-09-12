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
$my_user = $update->message->from->first_name;
$my_username = $update->message->from->username;
$my_message = $update->message->text;
$my_prev_bot_message = $update->message->reply_to_message;
$key_answer_array = array("problem", "issue", "need help", "not working");
//array('a' => "problem",'b' => "problem", 'c' => "need help", 'd' => "not working");


//your app
try {

    if($update->message->text == 'ciao')
    {
      $response = $client->sendMessage([
        'chat_id' => $update->message->chat->id,
//        'text' => "Hi, what's up!?"
        'text' => "Hi, $my_user, what's up!? What can I do for you?"
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
        'text' => "Let's open a Case for you..."
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

        $query_c = "SELECT sfid, AccountId, name FROM salesforce.contact WHERE telegram_handle__c = '@$my_username';";
        $result_c = pg_query($query_c) or die('Query failed: ' . pg_last_error());

        //$row_c = pg_fetch_row($result_c);
        $contact_id = pg_fetch_result($result_c, 0, 'sfid');
        $account_id = pg_fetch_result($result_c, 0, 'AccountId');
        $c_name  = pg_fetch_result($result_c, 0, 'name');

        $query = "INSERT INTO salesforce.case (ContactId, AccountId, Subject, Description, Priority, RecordTypeId, Status, BusinessHoursId) VALUES ('$contact_id','$account_id', 'Support Case', 'A Nice Support Case', 'Medium', '012240000002iSKAAY', 'New', '01m2400000001gYAAQ') RETURNING Id;";
        $result = pg_query($query) or die('Query failed: ' . pg_last_error());

        sleep(10);

        $row = pg_fetch_row($result);
        $new_id = $row[0];

        $query_sel = "SELECT casenumber FROM salesforce.case WHERE Id = '$new_id';";
        $result_sel = pg_query($query_sel) or die('Query failed: ' . pg_last_error());

        $row2 = pg_fetch_row($result_sel);
        $case_n = $row2[0];

        $response = $client->sendChatAction(['chat_id' => $update->message->chat->id, 'action' => 'typing']);
        $response = $client->sendMessage([
          'chat_id' => $update->message->chat->id,
          'text' => "Here is your Case Number: $case_n"
          ]);

        // free resultset
        pg_free_result($result);
        pg_free_result($result_sel);
        pg_free_result($result_c);
        // close connection
        pg_close($db);

    }
    else if(strpos_array($my_message, $key_answer_array) !== FALSE)
    {
    	$response = $client->sendChatAction(['chat_id' => $update->message->chat->id, 'action' => 'typing']);
    	$response = $client->sendMessage([
        	'chat_id' => $update->message->chat->id,
        	'text' => "Let's open a Case for you... Can you give me a short title for the problem?"
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





    // else
    // {
    // 	$response = $client->sendChatAction(['chat_id' => $update->message->chat->id, 'action' => 'typing']);
    // 	$response = $client->sendMessage([
    // 		'chat_id' => $update->message->chat->id,
    // 		'text' => "Invalid command, please use /help to get list of available commands"
    // 		]);
    // }


function strpos_array($haystack, $needles) {
    if ( is_array($needles) ) {
        foreach ($needles as $str) {
            if ( is_array($str) ) {
                $pos = strpos_array($haystack, $str);
            } else {
                $pos = strpos($haystack, $str);
            }
            if ($pos !== FALSE) {
                return $pos;
            }
        }
    } else {
        return strpos($haystack, $needles);
    }
}

} catch (\Zelenin\Telegram\Bot\NotOkException $e) {

    //echo error message ot log it
    //echo $e->getMessage();

}
