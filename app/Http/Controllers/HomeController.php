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
                        // получил id отправителя сообщения
                        $message = $vk_callback_event['object'];
                        $user_id = $message['from_id'];
                        $txt =  $message['text'];
                        $random_id = $message['random_id'];
                        $conversation_message_id = $message['conversation_message_id'];

                        // получаю его имя
                        $vk = new VKApiClient('5.101');
                        $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                            'user_ids' => [$user_id],
                        ));
                        $name = $response[0]['first_name'];

                        if ($txt == ("Привет" || "Начать") && $conversation_message_id == 1){
                            // отправляем сообщение приветствие
                            $vk = new VKApiClient('5.101');
                            $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                                'user_id' => $user_id,
                                'message' => "Добро пожаловать Милорд $name",
                                'random_id' => $random_id,
                            ));
                        } elseif ($conversation_message_id > 2){
                            // отправляем сообщение приветствие
                            $vk = new VKApiClient('5.101');
                            $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                                'user_id' => $user_id,
                                'message' => "$name скоро допилится функционал и вы сможите: 
1) переводить текст в голосовые сообщения
2) менять голос бота 
3) добавлять бота в чаты и переводить голосовые сообщения в текст
5) возможно многофункциональный переводчик, но это не точно:)
                         
Доброго дня)
                         ",
                                'random_id' => $random_id,
                            ));
                        }
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
