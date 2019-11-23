<?php

namespace App\Http\Controllers;

use App\Jobs\ChatRecognitionAudio;
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
                echo 'ok';
                try {
                    // выборка необходимый переменных
                    $object = $vk_callback_event['object']['message'];
                    if ($object['from_id'] < 0 ){
                        break;
                    } else {
                        $user_id = $object['from_id'];
                    }
                    $peer_id = $object['peer_id'];
                    $txt = $object['text'] ?? "";
                    $fwd_messages = $object['fwd_messages'];


                    // проверка на тип задачи
                    if (isset($object['payload'])) {
                        $payload = json_decode($object['payload'], true);

                        //отправляем задачу в очередь, ответы на стандартные дейтвия по кнопкам
                        $this -> dispatch(new GroupStandartAnswers($peer_id, $payload, $object));
                        break;

                    } else {
                        if(!empty($object['attachments']) && ($object['attachments'][0]['type'] == "audio_message") && ($user_id != $peer_id)) {

                            // распознования речи из чата
                            $audio_file = $object['attachments'][0]['audio_message']['link_ogg'];

                            // отправляем задачу в очередь
                            $this->dispatch(new ChatRecognitionAudio($peer_id, $audio_file, $user_id));
                            break;
                        }

                        // распознования речи
                        elseif (!empty($object['attachments']) && ($object['attachments'][0]['type'] == "audio_message") && ($user_id == $peer_id)) {

                            // отправляем задачу в очередь
                            $audio_file = $object['attachments'][0]['audio_message']['link_ogg'];
                            $this -> dispatch(new GroupRecognitionAudio($user_id, $audio_file));
                            break;
                        }

                        // если существуют вложенные голосоовые сообщения -> обрабатываем
                        elseif(!empty($fwd_messages)){
                                foreach ($fwd_messages as $fwd_message) {
                                    if ($fwd_message['attachments'][0]['type'] == "audio_message") {
                                        $audio_file = $fwd_message['attachments'][0]['audio_message']['link_ogg'];
                                        $user_id = $fwd_message['from_id'];

                                        // отправляем задачу в очередь
                                        $this->dispatch(new ChatRecognitionAudio($peer_id, $audio_file, $user_id));
                                    }
                            }
                                break;
                        }

                        // синтез речи
                        elseif (!empty($txt)  &&  empty($object['attachments']) && ($user_id == $peer_id)) {

                            // отправляем задачу в очередь
                            $this -> dispatch(new GroupSynthesisAudio($txt, $user_id));
                            break;
                        }
                        break;
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
