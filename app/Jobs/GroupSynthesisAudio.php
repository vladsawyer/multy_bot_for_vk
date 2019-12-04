<?php

namespace App\Jobs;

use App\Models\UserBot;
use CURLFile;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VK\Client\VKApiClient;

class GroupSynthesisAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $txt;
    protected $user_id;

    /**
     * Create a new job instance.
     *
     * @param $txt
     * @param $user_id
     */
    public function __construct($txt, $user_id)
    {
        $this -> txt = $txt;
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
        $audio_file = $this -> send_speechKit_synthesis($this -> txt, $this -> user_id);
        $attachment = $this -> vkApi_upload($this -> user_id, $audio_file);
        $this -> send_message($this -> user_id, $attachment);
        unlink($audio_file);
    }

    // отправка в yandex SpeechKit на синтез речи
    function send_speechKit_synthesis($txt, $user_id){
        //создаем имя и путь к этому файлу в локальном хранилище
        $audio_file =  storage_path('synthesis_audio')."/audio_$user_id".'_'.random_int(1,99999).'.ogg';
        if (file_exists($audio_file)) {
            return $audio_file;
        }

        // на случай переезда или еще каких-нибудь штук
        UserBot::firstOrCreate(
            [
                'vk_id' => $user_id
            ]
        );


        //получаем тип голоса для данного юзера
        $voice = UserBot::where('vk_id', $user_id) -> first() -> voice;

        // отправка текста в SpeechKit
        $url = "https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize";
        $post = http_build_query(array(
            'lang'    => 'ru-RU',
            "voice"   => $voice,
            'emotion' => 'neutral',
            'text'    => $txt,
        ));

        $headers = ['Authorization: Api-Key ' . getenv('YANDEX_API_TOKEN')];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($post !== false) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
        }
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            $decodedResponse = json_decode($response, true);
            echo "Error code: " . $decodedResponse["error_code"] . "\r\n";
            echo "Error message: " . $decodedResponse["error_message"] . "\r\n";
        } else {
            file_put_contents($audio_file, $response);
        }
        curl_close($ch);
        return $audio_file;
    }

    // Функция для загрузки файла на сервер вк
    function vkApi_upload($user_id, $audio_file) {

        // Получает адрес сервера для загрузки документа в личное сообщение
        $vk = new VKApiClient('5.103');
        $response = $vk->docs()->getMessagesUploadServer(getenv('VK_TOKEN'), array(
            'peer_id' => $user_id,
            'type' => 'audio_message',
        ));
        $upload_url = $response['upload_url'];

        //загружаем post запросом
        if (!file_exists($audio_file)) {
            throw new \Exception('File not found: '.$audio_file);
        }
        $curl = curl_init($upload_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($audio_file)));
        $json = curl_exec($curl);
        $error = curl_error($curl);
        if ($error) {
            throw new Exception("Failed {$upload_url} request");
        }
        curl_close($curl);
        $upload_audio= json_decode($json, true);
        if (!$upload_audio) {
            throw new Exception("Invalid response for {$upload_url} request");
        }
        //получаем строку для сохранения файла на серваке
        $file = $upload_audio['file'];

        // сохраненяем файл на серваке и получаем идификаторы для отправки файла
        $vk = new VKApiClient('5.103');
        $parameter = $vk->docs()->save(getenv('VK_TOKEN'), array(
            'file' => $file,
        ));

        // формируем поле attachment для отправки сообщения
        $attachment = 'doc'.$parameter['audio_message']['owner_id'].'_'. $parameter['audio_message']['id'].'_'.$parameter['audio_message']['access_key'] ;
        return $attachment;
    }

    //отправка голосового сообщения
    function send_message($user_id, $attachment){
        $vk = new VKApiClient('5.103');
        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
            'user_id' => $user_id,
            'attachment' => $attachment,
            'random_id' => random_int(1,9999999999),
        ));
    }

}
