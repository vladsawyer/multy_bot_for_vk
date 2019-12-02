<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VK\Client\VKApiClient;
use Exception;

class ChatRecognitionAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $peer_id;
    protected $object;
    protected $audio_file;
    protected $user_id;

    /**
     * Create a new job instance.
     * @param $peer_id
     * @param $audio_file
     * @param $user_id
     */
    public function __construct($peer_id, $audio_file, $user_id)
    {
        $this -> peer_id = $peer_id;
        $this -> audio_file = $audio_file;
        $this -> user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $data_audio_message = $this -> download_audio_message($this -> audio_file);
        $message = $this -> send_speechKit_recognition($data_audio_message);
        $name = $this -> get_name($this -> user_id);
        $this -> send_message($this -> peer_id, $message, $name);
    }

    function download_audio_message($audio_file){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $audio_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $data_audio_message = curl_exec($ch);
        curl_close($ch);
        return  $data_audio_message;
    }

    // отправка в yandex SpeechKit на распознование речи
    function send_speechKit_recognition($data_audio_message){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&format=oggopus");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Api-Key '. config('var.YANDEX_API_TOKEN')));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_audio_message);
        $res = curl_exec($ch);
        curl_close($ch);
        $decodedResponse = json_decode($res, true);
        if (!isset($decodedResponse["result"])) {
            echo "Error code: " . $decodedResponse["error_code"] . "\n";
            echo "Error message: " . $decodedResponse["error_message"] . "\n";
        }
        return $decodedResponse["result"];
    }

    // получаeм его имя
    function get_name($user_id){
        $vk = new VKApiClient('5.103');
                $response = $vk->users()->get(config('var.VK_TOKEN'), array(
            'user_ids' => [$user_id],
        ));
       return $name = $response[0]['first_name']. " " .$response[0]['last_name'];
    }

    //отправка переведенного сообщения
    function send_message($peer_id, $message, $name){
        $vk = new VKApiClient('5.103');
        $response = $vk->messages()->send(config('var.VK_TOKEN'), array(
            'peer_id' => $peer_id,
            'message' => "[$name]\n". $message,
            'random_id' => random_int(1,999999),
        ));

    }
}
