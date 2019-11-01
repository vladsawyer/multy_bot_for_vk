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
        $this -> getlog(json_encode($vk_callback_event));

        if ($vk_callback_event['secret'] !== getenv('VK_SECRET_TOKEN')) {
            return response('nioh');
        }


            switch ($vk_callback_event['type']){
                case 'confirmation':
                    return response(getenv('VK_CONFIRMATION_CODE'));
                    break;

                case 'message_new':
                    try{
                        $object = $vk_callback_event['object'];
                        $user_id = $object['from_id'];
                        $txt =  $object['text'];
                        $value_button =  $object['payload']['button'];

                        // получаю его имя
                        $vk = new VKApiClient('5.101');
                        $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                            'user_ids' => [$user_id],
                        ));
                        $name = $response[0]['first_name'];

                        require_once 'keyboards.php';


                        switch ( $value_button){
                            case  "start" :
                                $message = "Добро пожаловать $name! \n Я Мульти голосовой бот, разработанный [vladislav_nep | Непомнящих Владиславом], у меня есть свой сайт, его найдете в ссылках. \n Что я умею: \n 1️⃣ переводить текст в голосовые сообщения \n 2️⃣ Менять голос \n 3️⃣ Переводить голосовые сообщения в текст \n 4️⃣ Добавлять в чаты для автоматического перевода голосовых сообщений в текст \n 5️⃣ Повесилить вас историей для! \n \n Надеюсь я вам помогу или доставлю удовольствие!";
                                $value_keyboard = $keyboard_index;
                                break;

                            case  "speech_recognition" :
//                                $message = "$name скоро допилится функционал и вы сможите: \n 1) переводить текст в голосовые сообщения \n 2) менять голос бота \n 3) добавлять бота в чаты и переводить голосовые сообщения в текст \n 5) возможно многофункциональный переводчик, но это не точно:) \n \n Доброго дня)";
                                $message = "В разработке";
                                $value_keyboard = $keyboard_speech_recognition;
                                break;

                            case  "speech_synthesis" :
                                $message = "Синтез речи запущен, в разработке)";
                                $value_keyboard = $keyboard_speech_synthesis;

//                                return  redirect() -> route('Speech_SynthesisController@index');
                                break;

                            case  "voice" :
                                $message = "в разработке";
                                $value_keyboard = $keyboard_speech_synthesis_voice;
                                break;

                            case  "history_day" :
                                $message = "В разработке";
                                $value_keyboard = "";
                                break;

                            case "back_index" :
                                $message = "";
                                $value_keyboard = $keyboard_index;

                            default: $message = "Я вас не понял! Почему? \n 1) Неверная команда \n 2) Слишком длинный текст для синтеза речи \n 3) Аудио длиннее 30 сек для распознования речи"; break;
                        }

                            // отправляем сообщение
                            $vk = new VKApiClient('5.101');
                            $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                                'user_id' => $user_id,
                                'message' => $message,
                                'keyboard' => json_encode($value_keyboard),
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
