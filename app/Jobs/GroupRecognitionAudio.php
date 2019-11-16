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
    protected $object;
    protected $audio_file;

    /**
     * Create a new job instance.
     * @param $txt
     * @param $user_id
     * @param $object
     */
    public function __construct($txt, $user_id, $object)
    {
        $this -> txt = $txt;
        $this -> user_id = $user_id;
        $this -> audio_file = $object['attachments'][0]['audio_message']['link_ogg'];
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
        $this->getlog($audio_file_path);
        $message = $this -> send_speechKit_recognition($audio_file_path);
        $this -> send_message($this -> user_id, $message);
        fclose($audio_file_path);
        unlink($audio_file_path);
    }

    function download_audio_message($audio_file, $user_id){
        $ch = curl_init($audio_file);
        $audio_file_path = fopen(public_path('recognition_audio')."/audio_$user_id".'_'.random_int(1,9999999).".ogg" , 'w+b');
        curl_setopt($ch, CURLOPT_FILE, $audio_file_path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
       // fclose($audio_file_path);
        return $audio_file_path;
    }

    // отправка в yandex SpeechKit на распознование речи
    function send_speechKit_recognition($audio_file_path){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&format=oggopus");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Api-Key ' . getenv('YANDEX_API_TOKEN')));
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Api-Key ' . 'AQVN1SFv6RY9p5edudyFP2_93WhBjYQ24O5V3wx4'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

        curl_setopt($ch, CURLOPT_INFILE, $audio_file_path);
//        curl_setopt($ch, CURLOPT_INFILESIZE, '100000');
        $res = curl_exec($ch);
        curl_close($ch);
        $decodedResponse = json_decode($res, true);
        if (!isset($decodedResponse["result"])) {
            echo "Error code: " . $decodedResponse["error_code"] . "\n";
            echo "Error message: " . $decodedResponse["error_message"] . "\n";
        }
        $this->getlog($decodedResponse["result"]);
        return $decodedResponse["result"];

    }

    //отправка переведенного сообщения
    function send_message($user_id, $message){
        $vk = new VKApiClient('5.103', VKLanguage::RUSSIAN);
        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
//        $response = $vk->messages()->send('1b1e3a4a5c4880f6b80d56f7014137bf61339e8c72cda87b4202a7aea79dc7563491d1ed1187ace37f722', array(
            'user_id' => $user_id,
            'message' => $message,
            'random_id' => random_int(1,9999999999),
        ));

    }

    function getlog($msg){
        file_put_contents('php://stdout', $msg. "\n");
    }

}
