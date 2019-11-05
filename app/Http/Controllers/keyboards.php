<?php
    //главное меню
    $keyboard_index =
        [
            "one_time" => false,
            "buttons" => [
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"speech_recognition\"}",
                            "label" => "🗣 Распознование речи"
                        ],
                        "color" => "positive"
                    ],
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"speech_synthesis\"}",
                            "label" => "🤖 🤖Синтез речи"
                        ],
                        "color" => "positive"
                    ],
                ],
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"history_day\"}",
                            "label" => "🗣История дня"
                        ],
                        "color" => "positive"
                    ],
                ]
            ]

        ];

    // меню клавиатура синтеза речи
    $keyboard_speech_synthesis =
        [
            "one_time" => false,
            "buttons" => [
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"voice\"}",
                            "label" => "🗣Сменить голос"
                        ],
                        "color" => "positive"
                    ]
                ],
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"back_index\"}",
                            "label" => "🔙 🤖Назад"
                        ],
                        "color" => "negative"
                    ],
                ]
            ]

        ];

    // меню клавиатура синтеза речи для смены голоса
    $keyboard_speech_synthesis_voice =
        [
            "one_time" => false,
            "buttons" => [
//                [
//                    [
//                        "action" => [
//                            "type" => "text",
//                            "payload" => "{\"button\": \"voice_man\"}",
//                            "label" => "🗣 Мужчина"
//                        ],
//                        "color" => "positive"
//                    ],
//                    [
//                        "action" => [
//                            "type" => "text",
//                            "payload" => "{\"button\": \"voice_woman\"}",
//                            "label" => "🗣 Женщина"
//                        ],
//                        "color" => "positive"
//                    ]
//                ],
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"back_speech_synthesis\"}",
                            "label" => "🔙 🤖Назад"
                        ],
                        "color" => "negative"
                    ],
                ]
            ]

        ];

        $keyboard_speech_recognition =
            [
                "one_time" => false,
                "buttons" => [
                    [
                        [
                            "action" => [
                                "type" => "text",
                                "payload" => "{\"button\": \"speech_recognition_instructions\"}",
                                "label" => "🗣Как добавить бота в беседу"
                            ],
                            "color" => "positive"
                        ],

                    ],
                    [
                        [
                            "action" => [
                                "type" => "text",
                                "payload" => "{\"button\": \"back_index\"}",
                                "label" => "🔙 🤖Назад"
                            ],
                            "color" => "negative"
                        ],
                    ]
                ]

            ];

        $keyboard_speech_synthesis_back = [
            "one_time" => false,
            "buttons" => [
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"voice\"}",
                            "label" => "🗣Сменить голос"
                        ],
                        "color" => "positive"
                    ]
                ],
                [
                    [
                        "action" => [
                            "type" => "text",
                            "payload" => "{\"button\": \"back_index\"}",
                            "label" => "🔙 🤖Назад"
                        ],
                        "color" => "negative"
                    ],
                ]
            ]

        ];
