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

        try{
            switch ($vk_callback_event['type']){
                case 'confirmation':
                    return response(getenv('VK_CONFIRMATION_CODE'));
                    break;

                case 'message_new':


                        $object = $vk_callback_event['object'];
                        $user_id = $object['from_id'];
                        $txt =  $object['text'];
                        $random_id = $object['random_id'];
                        $conversation_message_id = $object['conversation_message_id'];

                        // получаю его имя
                        $vk = new VKApiClient('5.101');
                        $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                            'user_ids' => [$user_id],
                        ));
                        $name = $response[0]['first_name'];

                        switch ($txt){
                            case "Начать" : $message = "Добро пожаловать $name \n Вот список команд: \n 1) что умеешь"; break;
                            case  "Что умеешь" : $message = "$name скоро допилится функционал и вы сможите: \n 1) переводить текст в голосовые сообщения \n 2) менять голос бота \n 3) добавлять бота в чаты и переводить голосовые сообщения в текст \n 5) возможно многофункциональный переводчик, но это не точно:) \n \n Доброго дня)"; break;
                        }

                            // отправляем сообщение приветствие
                            $vk = new VKApiClient('5.101');
                            $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                                'user_id' => $user_id,
                                'message' => $message,
                                'random_id' => rand(),
                            ));

                            echo 'ok';
                            break;

                  }
            } catch (\VK\Exceptions\VKApiException $e){
                 $this -> getlog($e -> getMessage());
            }
    }

    function getlog($msg){
        file_put_contents('php://stdout', $msg. "\n");
    }

}
