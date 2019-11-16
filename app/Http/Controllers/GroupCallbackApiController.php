<?php

namespace App\Http\Controllers;

use App\Jobs\GroupStandartAnswers;
use App\Jobs\GroupRecognitionAudio;
use App\Jobs\GroupSynthesisAudio;

class GroupCallbackApiController extends Controller
{
    public function index()
    {
        header("HTTP/1.1 200 OK");
        $vk_callback_event = json_decode(file_get_contents("php://input"), true);

        if (!isset($vk_callback_event) || ($vk_callback_event['secret'] !== getenv('VK_SECRET_TOKEN'))) {
            return response('nioh');
        }

        switch ($vk_callback_event['type']) {
            case 'confirmation':
                return response(getenv('VK_CONFIRMATION_CODE'));
                break;

            case 'message_new':
                try {
                    $user_id = $vk_callback_event['object']['from_id'];
                    $txt = $vk_callback_event['object']['text'] ?? "";

                    // проверка на тип задачи
                    if (isset($vk_callback_event['object']['payload'])) {
                        $payload = json_decode($vk_callback_event['object']['payload'], true);
                        $this->getlog(json_encode($payload));
                        echo 'ok';

                        //отправляем задачу в очередь, ответы на стандартные дейтвия по кнопкам
                        $this -> dispatch(new GroupStandartAnswers($user_id, $payload, $vk_callback_event));
                        break;

                    } else {
                        $payload = null;
                        $this->getlog(json_encode($vk_callback_event));

                        if (isset($txt)) {
                            echo 'ok';
                            // синтез речи
                            // отправляем задачу в очередь
                            $this -> dispatch(new GroupSynthesisAudio($txt, $user_id));
                            break;

                        } elseif (isset($vk_callback_event['object']['message']['attachments']) && $vk_callback_event['object']['message']['attachments']['type'] === "audio_message") {
                            echo 'ok';
                            // распознования речи
                            $this -> dispatch(new GroupRecognitionAudio($txt, $user_id, $vk_callback_event));
                            break;
                        }
                    }
                } catch (\VK\Exceptions\VKApiException $e) {
                    $this->getlog($e->getMessage());
                }
        }
    }

    function getlog($msg){
        file_put_contents('php://stdout', $msg. "\n");
    }

}
