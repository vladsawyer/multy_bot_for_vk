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

        $vk_callback_event =  json_decode(file_get_contents("php://input"), true);
        $this -> getlog(json_encode($vk_callback_event));
        try{
            if ($vk_callback_event['secret'] !== getenv('VK_SECRET_TOKEN')) {
                return response('nioh');

            }

            switch ($vk_callback_event['type']){
                case 'confirmation':
                    return response(getenv('VK_CONFIRMATION_CODE'));
                    break;

                case 'message_new':
                    // получил id отправителя сообщения
                    $message = $vk_callback_event['object'];
                    $user_id = $message['from_id'];
                    $txt =  $message['text'];
                    $conversation_message_id = $message['conversation_message_id'];

                    // получаю его имя
                    $vk = new VKApiClient(5.102);
                    $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                        'user_ids' => [$user_id],
                    ));
                    $name = $response[0]['first_name'];

                    if ($txt === ('привет' || 'начать' )){
                        // отправляем сообщение приветствие
                        $vk = new VKApiClient(5.102);
                        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                            'user_id' => $user_id,
                            'message' => "Добро пожаловать Милорд $name",
                            'random_id' => $conversation_message_id
                        ));
                    }

                    echo 'ok';
                    break;
                default:
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
