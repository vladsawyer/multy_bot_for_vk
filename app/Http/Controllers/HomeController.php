<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

    /**
     * @return string
     */
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
                    //главное меню
                    $keyboard_index =
                        [
                            "one_time" => false,
                            "buttons" => [
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => "{\"command\": \"speech_recognition\"}",
                                            "label" => "Распознование речи"
                                        ],
                                        "color" => "positive"
                                    ],
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => "{\"command\": \"speech_synthesis\" }",
                                            "label" => "Синтез речи"
                                        ],
                                        "color" => "positive"
                                    ],
                                ],
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => "{\"command\": \"history_day\"}",
                                            "label" => "История дня"
                                        ],
                                        "color" => "positive"
                                    ],
                                ]
                            ]

                        ];

                    // меню клавиатура синтеза речи
                    $keyboard_speech_synthesis =
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
                                            "payload" => "{\"command\": \"back_index\"}",
                                            "label" => "Главная"
                                        ],
                                        "color" => "negative"
                                    ],
                                ]
                            ]

                        ];

                    // меню клавиатура синтеза речи для смены голоса
                    $keyboard_speech_synthesis_voice =
                        [
                            "one_time" => false,
                            "buttons" => [
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => json_encode(["command" => "choice_voice", "parametr_1" => "voice_man"]),
                                            "label" => "Мужчина"
                                        ],
                                        "color" => "positive"
                                    ],
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => json_encode(["command" => "choice_voice", "parametr_1" => "voice_woman"]),
                                            "label" => "Женщина"
                                        ],
                                        "color" => "positive"
                                    ]
                                ],
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" => json_encode([ "command" => "back_speech_synthesis"]),
                                            "label" => "Назад"
                                        ],
                                        "color" => "negative"
                                    ],
                                ]
                            ]

                        ];

                    // клавиатура распознования речи
                    $keyboard_speech_recognition =
                        [
                            "one_time" => false,
                            "buttons" => [
                                [
                                    [
                                        "action" => [
                                            "type" => "text",
                                            "payload" =>  json_encode(["command" => "speech_recognition_instructions"]),
                                            "label" => "Как добавить бота в беседу"
                                        ],
                                        "color" => "positive"
                                    ],

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
                        $user_id = $object['from_id'] ?? 0;
                        $txt = $object['text'] ?? "";

                        if (isset($object['payload'])){
                            $payload = json_decode($object['payload'], true);
                            $this->getlog(json_encode($payload));
                        } else{
                            $payload  = null;
                            $this->getlog(json_encode($vk_callback_event));
                        }


                        // получаю его имя
                        $vk = new VKApiClient('5.103');
                        $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                            'user_ids' => [$user_id],
                        ));
                        $name = $response[0]['first_name'];


                        switch ($payload['command']) {
                            case  "start" :
                                $message = "Добро пожаловать $name! \n Я Мульти голосовой бот, разработчик [vladislav_nep | Непомнящих Владислав], у меня есть свой сайт, его найдете в ссылках. \n Что я умею: \n 1️⃣ переводить текст в голосовые сообщения  \n 2️⃣ Менять голос \n 3️⃣ Переводить голосовые сообщения в текст \n 4️⃣ Добавлять в чаты для автоматического перевода голосовых сообщений в текст \n 5️⃣ Повесилить вас историей для! \n Если не видите кнопок, то используйте цифры как команды. \n \n Надеюсь я вам помогу или доставлю удовольствие!";
                                $send_value_keyboard = $keyboard_index;
                                break;

                            case  "speech_recognition" :
                                $message = "Отправьте голосовое сообщение до 30 секунд! В разработке)";
                                $send_value_keyboard = $keyboard_speech_recognition;
                                break;

                            case "speech_recognition_instructions":
                                $message = "Здесь будет инструкция, пока лень писать)";
                                $send_value_keyboard = $keyboard_speech_recognition;
                                break;

                            case "back_index" :
                                $message = "Выбирете команду";
                                $send_value_keyboard = $keyboard_index;
                                break;

                            case  "speech_synthesis" :
                                $message = "Синтез речи запущен, в разработке)";
                                $send_value_keyboard = $keyboard_speech_synthesis;
                                break;

                            case "back_speech_synthesis":
                                $message = "Синтез речи запущен, в разработке";
                                $send_value_keyboard = $keyboard_speech_synthesis;
                                break;

                            case "voice" :
                                $message = "Выберите голос";
                                $send_value_keyboard = $keyboard_speech_synthesis_voice;
                                break;

                            case "choice_voice":
                                switch ($payload['parametr_1']){
                                    case "voice_man":
                                        $message  = "Смена голоса будет доступна в последнию очередь \n Выбран голос: Мужчина";
                                        $send_value_keyboard = "";
                                        break;
                                    case "voice_woman":
                                        $message  = "Смена голоса будет доступна в последнию очередь \n Выбран голос: Женщина";
                                        $send_value_keyboard = "";
                                        break;
                                    default:
                                        $message = "Тип не распознан";
                                        $send_value_keyboard = $keyboard_speech_synthesis_voice;
                                        break;
                                }
                                break;


                            case "history_day":
                                $message = "В разработке";
                                $send_value_keyboard = "";
                                break;

                            default:
                                $message = "Я вас не понял! Почему? \n 1) Команды осуществляются только при помощи кнопок \n 2) Слишком длинный текст для синтеза речи \n 3) Аудио длиннее 30 сек для распознования речи";
                                $send_value_keyboard = $keyboard_index;
                                break;

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

                  }

    }

    function getlog($msg){
        file_put_contents('php://stdout', $msg. "\n");
    }

}
