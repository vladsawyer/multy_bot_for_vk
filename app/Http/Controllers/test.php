<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class test extends Controller
{
    public function index(){
        $response_1 = json_encode(
            [
                'response' =>
                    [
                        0 =>
                            [
                                'id' => 220707135,
                                'owner_id' => 185014513,
                                'title' => 'test',
                                'size' => 380652,
                                'ext' => 'txt',
                                'url' => 'http://vk.com/doc...d7684b5e2&api=1',
                            ],
                    ],
            ]
        );

        $response = json_decode($response_1);

        $attachment = 'audio'.$response[0]['owner_id'].'_'. $response[0]['id'];
    }

}
