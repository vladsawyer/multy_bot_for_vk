<?php

namespace App\Http\Controllers;

use App\Models\UserBot;
use VK\Client\VKApiClient;

class HomeController extends Controller
{
//    /**
//     * Create a new controller instance.
//     *
//     * @return void
//     */
//    public function __construct()
//    {
//        $this->middleware('auth');
//    }

    public function index()
    {
        header("HTTP/1.1 200 OK");
        $vk_callback_event =  json_decode(file_get_contents("php://input"), true);

        if ($vk_callback_event['secret'] !== getenv('VK_SECRET_TOKEN')) {
            return response('nioh');
        }

        switch ($vk_callback_event['type']){
                case 'confirmation':
                    return response(getenv('VK_CONFIRMATION_CODE'));
                    break;

                case 'message_new':

                    // клавиатура
                    // меню главная
                    $keyboard_index =
                        [
                            "one_time" => false,
                            "buttons" => [
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => json_encode([ "command" => "voice"]),
                                            "label" => "Сменить голос"
                                        ],
                                        "color" => "positive"
                                    ]
                                ],
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" =>  json_encode(["command" => "speech_recognition_instructions"]),
                                            "label" => "Как добавить бота в беседу"
                                        ],
                                        "color" => "positive"
                                    ],

                                ]
                            ]

                        ];

                    // меню для смены голоса
                    $keyboard_speech_synthesis_voice =
                        [
                            "one_time" => false,
                            "buttons" => [
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => json_encode(["command" => "choice_voice", "parametr_1" => "voice_man"]),
                                            "label" => "Филип"
                                        ],
                                        "color" => "positive"
                                    ],
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => json_encode(["command" => "choice_voice", "parametr_1" => "voice_woman"]),
                                            "label" => "Алена"
                                        ],
                                        "color" => "positive"
                                    ]
                                ],
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => "{\"command\": \"back_index\"}",
                                            "label" => "Главная"
                                        ],
                                        "color" => "negative"
                                    ],
                                ]
                            ]

                        ];


                    try {
                        $object = $vk_callback_event['object']['message'] ?? [];
                        $user_id = $object['from_id'];
                        $txt = $object['text'] ?? "";

                        // получаю его имя
                        $vk = new VKApiClient('5.103');
                        $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                            'user_ids' => [$user_id],
                        ));
                        $name = $response[0]['first_name'];

                        if (isset($object['payload'])){
                            $payload = json_decode($object['payload'], true);
                            $this->getlog(json_encode($payload));

                            switch ($payload['command']) {
                                case  "start" :
                                    $user_bot = UserBot::firstOrCreate(
                                        [
                                            'vk_id' => $user_id
                                        ]
                                    );
                                    $message = "Добро пожаловать $name! \n Я MultyVoiceBot, разработчик @vladislav_nep(Непомнящих Владислав), у меня есть свой сайт, его найдете в ссылках. \n Что я умею: \n 1️⃣ Переводить текст в голосовые сообщения  \n 2️⃣ Менять голос \n 3️⃣ Переводить голосовые сообщения в текст \n 4️⃣ Добавлять в чаты для автоматического перевода голосовых сообщений в текст \n \n Для синтеза  речи отправьте любой текст. \n Для распознования речи отправьте голосовое сообщение до 30 секунд. \n \n Надеюсь я вам помогу или доставлю удовольствие!";
                                    $send_value_keyboard = $keyboard_index;
                                    break;

                                case "speech_recognition_instructions":
                                    $message = "Здесь будет инструкция, пока лень писать)";
                                    $send_value_keyboard = "";
                                    break;

                                case "back_index" :
                                    $message = "Продолжим)";
                                    $send_value_keyboard = $keyboard_index;
                                    break;

                                case "voice" :
                                    $message = "Выберите голос";
                                    $send_value_keyboard = $keyboard_speech_synthesis_voice;
                                    break;

                                case "choice_voice":
                                    switch ($payload['parametr_1']){
                                        case "voice_man":
                                            UserBot::updateOrCreate(
                                                ['vk_id' => $user_id],
                                                ['voice' => 'filipp']
                                            );
                                            $message  = "Выбран голос: Филип";
                                            $send_value_keyboard = $keyboard_index;
                                            break;
                                        case "voice_woman":
                                            UserBot::updateOrCreate(
                                                ['vk_id' => $user_id],
                                                ['voice' => 'alena']
                                            );
                                            $message  = "Выбран голос: Алена";
                                            $send_value_keyboard = $keyboard_index;
                                            break;
                                        default:
                                            $message = "Тип не распознан";
                                            $send_value_keyboard = $keyboard_speech_synthesis_voice;
                                            break;
                                    }
                                    break;

                                default:
                                    if (isset($object['payload'])){
                                        $message = "Команда не распознана";
                                        $send_value_keyboard = $keyboard_index;
                                        break;
                                    }
                            }

                        } else {
                            $payload  = null;
                            $this->getlog(json_encode($vk_callback_event));

                            if (isset($txt)){
                                // синтез речи

                                //получаем тип голоса для данного юзера
                                $user = UserBot::where('vk_id', $user_id) -> first();
                                $voice = $user -> voice;

                                //отправляем запрос в SpeechKit
                                $this -> SendSpeechKitSynthesis($txt, $voice);

                                $message = "проверка и тест";
                                $send_value_keyboard = "";
                                break;

                            } elseif (isset($vk_callback_event['object']['message']['attachments']) && $vk_callback_event['object']['message']['attachments']['type'] === "audio_message"){
                                // распознования речи
                                $message = "потом";
                                $send_value_keyboard = "";
                                break;

                            }

                        }

                        // отправляем сообщение
                        $vk = new VKApiClient('5.103');
                        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                            'user_id' => $user_id,
                            'message' => $message,
                            'keyboard' => json_encode($send_value_keyboard),
                            'random_id' => rand(),
                        ));
                        echo 'ok';
                        break;

                    } catch (\VK\Exceptions\VKApiException $e){
                        $this -> getlog($e -> getMessage());
                    }
                    break;

                  }

    }

    function getlog($msg){
        file_put_contents('php://stdout', $msg. "\n");
    }

    function SendSpeechKitSynthesis($txt, $voice){
        $file_name =  public_path('speech_audio').'/audio_'.md5($txt).'.ogg';
        if (file_exists($file_name)) {
            return $file_name;
        }
        $file_handler = fopen($file_name, 'w+');

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
            file_put_contents($file_name, $response);
        }

        //для просмотра логов
        $this -> getlog(json_decode($response));

        curl_close($ch);
        fclose($file_handler);

        return $file_name;
    }

    function SendSpeechKitRecognition(){
        define('YANDEX_API_ENDPOINT', "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize");


        $audioFileName = "speech.ogg";
        $file = fopen($audioFileName, 'rb');

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
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($audioFileName));
        $res = curl_exec($ch);
        curl_close($ch);
        $decodedResponse = json_decode($res, true);
        if (isset($decodedResponse["result"])) {
            echo $decodedResponse["result"];
        } else {
            echo "Error code: " . $decodedResponse["error_code"] . "\r\n";
            echo "Error message: " . $decodedResponse["error_message"] . "\r\n";
        }

        fclose($file);

    }


}
