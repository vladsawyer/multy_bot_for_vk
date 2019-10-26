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

//    /**
//     * Show the application dashboard.
//     *
//     * @return string
//     */
    public function index()
    {
        header($_SERVER['SERVER_PROTOCOL'].'200 OK');

        $vk_callback_event_0 = file_get_contents("php://input");
        $vk_callback_event = json_decode($vk_callback_event_0);

        try{
            if ($vk_callback_event -> secret !== getenv('VK_SECRET_TOKEN')) {
                return response('nioh');

            }

            switch ($vk_callback_event -> type){
                case 'confirmation':
                    return response(getenv('VK_CONFIRMATION_CODE'));
                    break;

                case 'message_new':
                    // получил id отправителя сообщения
                    $user_id = $vk_callback_event['object'] -> from_id;
                    $txt = $vk_callback_event['object'] -> text;

                    // получаю его имя
                    $vk = new VKApiClient();
                    $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                        'user_ids' => [$user_id],
                    ));
                    $name = $response[0]['first_name'];

                    if ($txt === ('привет' || 'начать' )){
                        // отправляем сообщение приветствие
                        $vk = new VKApiClient();
                        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                            'user_id' => $user_id,
                            'message' => "Добро пожаловать Милорд $name",
                        ));
                    }

                    return response('ok');
                    break;
                default:
                    return response('ok');
                    break;
            }

        } catch (\Exception $ex) {
            //Выводим сообщение об исключении.
            echo $ex->getMessage();
        }


    }


}
