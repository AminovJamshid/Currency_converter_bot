<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$token = "7408239461:AAFxgYyzneaEs3WUi340_AkrlBxZEy_ht6Y";
$tgApi = "https://api.telegram.org/bot$token/";

$client = new Client(['base_uri' => $tgApi]);

$currency = new Client(['base_uri'=>'https://cbu.uz/oz/arkhiv-kursov-valyut/json/']);

$curresies = [];
$data = json_decode($currency -> getBody() -> getContents(), true);

foreach ($data as $item) {
    $currencies[strtolower($item['Ccy'])] = $item['Rate'];
}

$update = json_decode(file_get_contents('php://input'));
var_dump($update);
if (isset($update)) {
    if (isset($update->message)) {
        $message = $update->message;
        $chat_id = $message->chat->id;
        $miid = $message->message_id;
        $name = $message->from->first_name;
        $fromid = $message->from->id;
        $text = $message->text;
        $photo = $message->photo ?? '';
        $video = $message->video ?? '';
        $audio = $message->audio ?? '';
        $voice = $message->voice ?? '';
        $reply = $message->reply_markup ?? '';

        $exp = explode('-', $text);

        if (count($exp) == 2) {
            $amount = floatval($exp[0]);
            $currencyPair = strtolower($exp[1]);

            if ($currencyPair === 'usd-uzs' && isset($currencies['usd'])) {
                $rate = $currencies['usd'];
                $exchangeAmount = $amount * $rate;
                $responseText = "$amount USD is equivalent to $exchangeAmount UZS.";
            } elseif ($currencyPair === 'uzs-usd' && isset($currencies['usd'])) {
                $rate = $currencies['usd'];
                $exchangeAmount = $amount / $rate;
                $responseText = "$amount UZS is equivalent to $exchangeAmount USD.";
            } else {
                $responseText = "Please use the format 'amount-usd-uzs' or 'amount-uzs-usd'. For example, '100-usd-uzs'.";
            }

            $client->post('sendMessage', [
                'form_params' => [
                    'chat_id' => $chat_id,
                    'text' => $responseText
                ]
            ]);
        }
    }
}
