<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GroupRecognitionAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $txt;
    protected $voice;

    /**
     * Create a new job instance.
     *
     * @param $txt
     * @param $voice
     */
    public function __construct($txt, $voice)
    {
        $this -> txt = $txt;
        $this -> voice = $voice;
    }

    /**
     * Execute the job.
     *
     * @param string $txt
     * @param string $voice
     * @return void
     */
    public function handle()
    {
       $this -> SendSpeechKitSynthesis($this->txt, $this->voice);

    }

    // отправка в yandex SpeechKit на синтез речи
    function SendSpeechKitSynthesis($txt, $voice){

        $file_name =  public_path('synthesis_audio').'/audio_'.md5($txt).'.ogg';
        if (file_exists($file_name)) {
            return $file_name;
        }

        $url = "https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize";
        $post = http_build_query(array(
            'lang'    => 'ru-RU',
            "voice"   => $voice,
            'emotion' => 'neutral',
            'text'    => $txt,
        ));

       $headers = ['Authorization: Api-Key ' . getenv('YANDEX_API_TOKEN')];
//        $headers = ['Authorization: Api-Key ' . "AQVN1SFv6RY9p5edudyFP2_93WhBjYQ24O5V3wx4"];
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
            file_put_contents($file_name, $response);
        }

        curl_close($ch);

        return $file_name;

    }
}
