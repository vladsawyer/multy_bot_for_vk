<?php

namespace App\Jobs;

use App\Models\UserBot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;

class GroupStandartAnswers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $name;
    protected $payload;
    protected $object;
    protected $keyboard;


    /**
     * Create a new job instance.
     *
     * @param $user_id
     * @param $name
     * @param $payload
     * @param $object
     */
    public function __construct($user_id, $payload, $object)
    {
        $this -> user_id = $user_id;
        $this -> payload = $payload;
        $this -> object = $object;
        $this -> keyboard = $keyboard = [
            'keyboard_index' =>
                [
                    "one_time" => false,
                    "buttons" => [
                        [
                            [
                                "action" => [
                                    "type" => "text",
                                    "payload" => "{\"command\": \"voice\"}",
                                    "label" => "Сменить голос"
                                ],
                                "color" => "primary"
                            ]
                        ],
                        [
                            [
                                "action" => [
                                    "type" => "text",
                                    "payload" => "{\"command\": \"speech_recognition_instructions\"}",
                                    "label" => "Как добавить бота в беседу"
                                ],
                                "color" => "primary"
                            ],

                        ],
                    ]
                ],

            'keyboard_speech_synthesis_voice' =>
                [
                    "one_time" => false,
                    "buttons" => [
                        [
                            [
                                "action" => [
                                    "type" => "text",
                                    "payload" =>  "{\"command\": \"choice_voice\", \"parametr_1\": \"voice_man\"}",
                                    "label" => "Филип"
                                ],
                                "color" => "positive"
                            ],
                            [
                                "action" => [
                                    "type" => "text",
                                    "payload" => "{\"command\": \"choice_voice\", \"parametr_1\": \"voice_woman\"}",
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

                ],
            'start' =>
                [
                    "one_time" => false,
                    "buttons" => [
                        [
                            [
                                "action" => [
                                    "type" => "text",
                                    "payload" => "{\"command\": \"start\"}",
                                    "label" => "Главная"
                                ],
                                "color" => "secondary"
                            ],
                        ]
                    ]

                ]
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this -> Command_Keyboard($this -> user_id, $this -> payload, $this -> object, $this -> keyboard);
    }

    function Command_Keyboard( $user_id, $payload, $object, $keyboard){
        switch ($payload['command']) {
            case  "start" :
                // получаю его имя
                $vk = new VKApiClient('5.103', VKLanguage::RUSSIAN);
                $response = $vk->users()->get(getenv('VK_TOKEN'), array(
//                $response = $vk->users()->get('1b1e3a4a5c4880f6b80d56f7014137bf61339e8c72cda87b4202a7aea79dc7563491d1ed1187ace37f722', array(
                    'user_ids' => [$user_id],
                ));
                $name = $response[0]['first_name'];

                UserBot::firstOrCreate(
                    [
                        'vk_id' => $user_id
                    ]
                );
                $message = "Добро пожаловать $name! \n Я MultyVoiceBot, разработчик @vladislav_nep(Непомнящих Владислав), у меня есть свой сайт, его найдете в ссылках. \n Что я умею: \n 1️⃣ Переводить текст в голосовые сообщения  \n 2️⃣ Менять голос \n 3️⃣ Переводить голосовые сообщения в текст \n 4️⃣ Добавлять в чаты для автоматического перевода голосовых сообщений в текст \n \n Для синтеза  речи отправьте любой текст. \n Для распознования речи отправьте голосовое сообщение до 30 секунд. \n \n Надеюсь я вам помогу или доставлю удовольствие!";
                $send_value_keyboard = $keyboard['keyboard_index'];
                break;

            case "speech_recognition_instructions":
                $message = "Здесь будет инструкция, пока лень писать)";
                $send_value_keyboard = "";
                break;

            case "back_index" :
                $message = "Продолжим)";
                $send_value_keyboard = $keyboard['keyboard_index'];
                break;

            case "voice" :
                $message = "Выберите голос";
                $send_value_keyboard = $keyboard['keyboard_speech_synthesis_voice'];
                break;

            case "choice_voice":
                switch ($payload['parametr_1']) {
                    case "voice_man":
                        UserBot::updateOrCreate(
                            ['vk_id' => $user_id],
                            ['voice' => 'filipp']
                        );
                        $message = "Выбран голос: Филип";
                        $send_value_keyboard = $keyboard['keyboard_index'];
                        break;
                    case "voice_woman":
                        UserBot::updateOrCreate(
                            ['vk_id' => $user_id],
                            ['voice' => 'alena']
                        );
                        $message = "Выбран голос: Алена";
                        $send_value_keyboard = $keyboard['keyboard_index'];
                        break;
                    default:
                        $message = "Тип не распознан";
                        $send_value_keyboard = $keyboard['keyboard_speech_synthesis_voice'];
                        break;
                }
                break;

            default:
                if (isset($object['payload'])) {
                    $message = "Команда не распознана";
                    $send_value_keyboard = $keyboard['start'];
                    break;
                }
        }

        // отправляем сообщение
        $vk = new VKApiClient('5.103', VKLanguage::RUSSIAN);
        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
//        $response = $vk->messages()->send('1b1e3a4a5c4880f6b80d56f7014137bf61339e8c72cda87b4202a7aea79dc7563491d1ed1187ace37f722', array(
            'user_id' => $user_id,
            'message' => $message,
            'keyboard' => json_encode($send_value_keyboard),
            'random_id' => random_int(1,9999999999),
        ));


    }
}
