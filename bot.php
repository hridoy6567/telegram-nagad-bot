<?php

$token = "8105479395:AAHm56StWriawZXRZUaVhb1J2_w3U9OT5vA";
$website = "https://api.telegram.org/bot$token";

// ডেটা রিসিভ
$update = json_decode(file_get_contents("php://input"), true);
$message = $update["message"];
$text = $message["text"];
$chat_id = $message["chat"]["id"];

if ($text == "/start") {
    sendMessage($chat_id, "স্বাগতম! আপনার নগদ স্ক্রিনশট তৈরি করতে নম্বর দিন:");
    file_put_contents("step_$chat_id.txt", "number");
} else {
    $step_file = "step_$chat_id.txt";
    $step = file_exists($step_file) ? file_get_contents($step_file) : "";

    if ($step == "number") {
        file_put_contents("data_$chat_id.txt", $text . "\n");
        file_put_contents($step_file, "trxid");
        sendMessage($chat_id, "ট্রানজেকশন আইডি দিন:");
    } elseif ($step == "trxid") {
        file_put_contents("data_$chat_id.txt", file_get_contents("data_$chat_id.txt") . $text . "\n");
        file_put_contents($step_file, "amount");
        sendMessage($chat_id, "পরিমাণ টাকা দিন:");
    } elseif ($step == "amount") {
        file_put_contents("data_$chat_id.txt", file_get_contents("data_$chat_id.txt") . $text . "\n");
        file_put_contents($step_file, "charge");
        sendMessage($chat_id, "চার্জ কত টাকা:");
    } elseif ($step == "charge") {
        file_put_contents("data_$chat_id.txt", file_get_contents("data_$chat_id.txt") . $text . "\n");
        file_put_contents($step_file, "done");

        $data = explode("\n", file_get_contents("data_$chat_id.txt"));
        $number = $data[0];
        $trxid = $data[1];
        $amount = $data[2];
        $charge = $data[3];
        $total = $amount + $charge;
        $time = date("d M Y, h:i A");

        $reply = "সেন্ড মানি সফল ✅\n\nনম্বর: $number\nট্রানজেকশন আইডি: $trxid\nপরিমাণ: $amount টাকা\nখরচ: $charge টাকা\nসর্বমোট: $total টাকা\nসময়: $time";
        sendMessage($chat_id, $reply);
        unlink($step_file);
        unlink("data_$chat_id.txt");
    } else {
        sendMessage($chat_id, "দয়া করে /start লিখে শুরু করুন।");
    }
}

// ফাংশন
function sendMessage($chat_id, $text) {
    global $website;
    file_get_contents($website . "/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode($text));
}

?>
