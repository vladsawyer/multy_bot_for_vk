<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VK\Client\Enums\VKLanguage;
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
        $file_path = storage_path('recognition_audio')."/audio_". $this -> peer_id. '_' .random_int(1,99999).'.ogg';
        $this -> download_audio_message($this -> audio_file, $file_path);
        $message = $this -> send_speechKit_recognition($file_path);
        $name = $this -> get_name($this -> user_id);
        $this -> send_message($this -> peer_id, $message, $name);
        unlink($file_path);
    }

    function download_audio_message($audio_file, $file_path){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $audio_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $data = curl_exec($ch);
        file_put_contents($file_path, $data);
        curl_close($ch);
    }

    // отправка в yandex SpeechKit на распознование речи
    function send_speechKit_recognition($file_path){
        $audio_file_path = fopen( $file_path, 'rb');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&format=oggopus");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Api-Key ' .getenv('YANDEX_API_TOKEN')));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_INFILE, $audio_file_path);
        $res = curl_exec($ch);
        curl_close($ch);
        $decodedResponse = json_decode($res, true);
        if (!isset($decodedResponse["result"])) {
            echo "Error code: " . $decodedResponse["error_code"] . "\n";
            echo "Error message: " . $decodedResponse["error_message"] . "\n";
        }
        fclose($audio_file_path);
        return $decodedResponse["result"];
    }

    // получаeм его имя
    function get_name($user_id){
        $vk = new VKApiClient('5.103', VKLanguage::RUSSIAN);
                $response = $vk->users()->get(getenv('VK_TOKEN'), array(
            'user_ids' => [$user_id],
        ));
       return $name = $response[0]['first_name']. " " .$response[0]['last_name'];
    }

    //отправка переведенного сообщения
    function send_message($peer_id, $message, $name){
        $vk = new VKApiClient('5.103', VKLanguage::RUSSIAN);
        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
            'peer_id' => $peer_id,
            'message' => "[$name]\n". $message,
            'random_id' => random_int(1,999999),
        ));

    }
}
