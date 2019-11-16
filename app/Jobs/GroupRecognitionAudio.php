<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;

class GroupRecognitionAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $txt;
    protected $user_id;
    protected $vk_callback_event;
    protected $audio_file;

    /**
     * Create a new job instance.
     * @param $txt
     * @param $user_id
     * @param $vk_callback_event
     */
    public function __construct($txt, $user_id, $vk_callback_event)
    {
        $this -> txt = $txt;
        $this -> user_id = $user_id;
        $this -> vk_callback_event = $vk_callback_event;
        $this -> audio_file = $vk_callback_event['object']['message']['attachments']['link_ogg'];
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $audio_file_path = $this -> download_audio_message($this -> audio_file, $this -> user_id);
        $message = $this -> send_speechKit_recognition($audio_file_path);
        $this -> send_message($this -> user_id, $message);
        fclose($audio_file_path);
    }

    function download_audio_message($audio_file, $user_id){
        $ch = curl_init($audio_file);
        $audio_file_path = fopen(public_path('recognition_audio')."/audio_$user_id".'_'.random_int(1,99999999) , 'wb');
        curl_setopt($ch, CURLOPT_FILE, $audio_file_path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        $this->getlog($audio_file_path);
        return $audio_file_path;
    }

    // отправка в yandex SpeechKit на распознование речи
    function send_speechKit_recognition($audio_file_path){
        define('YANDEX_API_ENDPOINT', "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize");


        $file = fopen($audio_file_path, 'rb');

        $query = http_build_query(array(
            'lang'    => 'ru-RU',
            'format'  => 'oggopus',
        ));
        $url = YANDEX_API_ENDPOINT.'?'.$query;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Api-Key ' . getenv('YANDEX_API_TOKEN')));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

        curl_setopt($ch, CURLOPT_INFILE, $file);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($audio_file_path));
        $res = curl_exec($ch);
        curl_close($ch);
        $decodedResponse = json_decode($res, true);
        if (!isset($decodedResponse["result"])) {
            echo "Error code: " . $decodedResponse["error_code"] . "\r\n";
            echo "Error message: " . $decodedResponse["error_message"] . "\r\n";
        }
        $this->getlog($decodedResponse["result"]);
        return $decodedResponse["result"];

    }

    //отправка переведенного сообщения
    function send_message($user_id, $message){
        $vk = new VKApiClient('5.103', VKLanguage::RUSSIAN);
        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
            'user_id' => $user_id,
            'message' => $message,
            'random_id' => random_int(1,9999999999),
        ));

    }

    function getlog($msg){
        file_put_contents('php://stdout', $msg. "\n");
    }

}
