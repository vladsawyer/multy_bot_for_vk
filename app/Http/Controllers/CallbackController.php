<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use VK\Client\VKApiClient;

class CallbackController extends Controller
{
//   public function verify(){
//       header('Content-Type: text/html; charset=ASCII');
//       header('HTTP/1.1 200 OK');
//
//
//       // получаю json события
//       $vk_callback_event = json_decode(file_get_contents('php://input'));
//       //для удобного логирования данных
//       function mylog($str){
//           file_put_contents('php://stdout', "$str\n");
//       }
//     mylog($vk_callback_event);
////
////       if (!$vk_callback_event){
////           return 'nioh';
////       }
//
//     try{
//         if ($vk_callback_event -> secret !== getenv('VK_SECRET_TOKEN')) {
//             return $php_errormsg;
//         }
//
//         switch ($vk_callback_event -> type){
//             case 'confirmation':
//                 return getenv('VK_CONFIRMATION_CODE');
//
//             case 'message_new':
//                 // получил id отправителя сообщения
//                 $user_id = $vk_callback_event['object'] -> from_id;
//                 $txt = $vk_callback_event['object'] -> text;
//
//                 // получаю его имя
//                 $vk = new VKApiClient();
//                 $response = $vk->users()->get(getenv('VK_TOKEN'), array(
//                     'user_ids' => [$user_id],
//                 ));
//                 $name = $response[0]['first_name'];
//
//                 if ($txt === ('привет' || 'начать' )){
//                     // отправляем сообщение приветствие
//                     $vk = new VKApiClient();
//                     $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
//                         'user_id' => $user_id,
//                         'message' => "Добро пожаловать Милорд $name",
//                     ));
//                 }
//
//                 return 'ok';
//             default:
//                 return 'ok';
//         }
//
//     } catch (\Exception $exception){
//         $this -> log_error($exception);
//     }
//
//
//
//   }
//
//    function log_error($message) {
//        if (is_array($message)) {
//            $message = json_encode($message);
//        }
//        $this -> _log_write('[ERROR] ' . $message);
//    }
//
//    function _log_write($message) {
//        $trace = debug_backtrace();
//        $function_name = isset($trace[2]) ? $trace[2]['function'] : '-';
//        $mark = date("H:i:s") . ' [' . $function_name . ']';
//        $log_name = BOT_LOGS_DIRECTORY.'/log_' . date("j.n.Y") . '.txt';
//        file_put_contents($log_name, $mark . " : " . $message . "\n", FILE_APPEND);
//    }



}
