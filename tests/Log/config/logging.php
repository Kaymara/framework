<?php
return [
    'default'  => null, // todo: pull value from env(APP_LOG) and default to file
    'channels' => [
        'file'                 => [
            'name'       => 'file',
            'handlers'   => [
                [
                    'name'           => \Monolog\Handler\StreamHandler::class,
                    'stream'         => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log',
                    'level'          => 'debug',
                    'bubble'         => true,
                    'filePermission' => null,
                    'useLocking'     => false,
                    'processors'     => [],
                    'formatter'      => null
                ],
            ],
            'processors' => [
                [
                    'level'     => 'debug',
                    'processor' => \Monolog\Processor\IntrospectionProcessor::class
                ],
                [
                    'level'     => 'notice',
                    'processor' => function ($record) {
                        $record['extra']['foo'] = 'bar';

                        return $record;
                    }
                ]
            ],
        ],
        'noHandler'            => [
            'name'     => 'noHandler',
            'handlers' => [
                [
                    'stream' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log',
                ],
            ],
        ],
        'wrongHandler'         => [
            'name'     => 'wrongHandler',
            'handlers' => [
                [
                    'name' => \Tests\Log\Stub::class,
                ],
            ],
        ],
        'noStream'             => [
            'name'     => 'noStream',
            'handlers' => [
                [
                    'name' => \Monolog\Handler\StreamHandler::class,
                ],
            ],
        ],
        'noProcessor'          => [
            'name'       => 'noProcessor',
            'processors' => [
                [
                    //
                ],
            ],
        ],
        'intProcessor'         => [
            'name'       => 'intProcessor',
            'processors' => [
                [
                    'processor' => 1
                ],
            ],
        ],
        'wrongProcessor'       => [
            'name'       => 'wrongProcessor',
            'processors' => [
                [
                    'processor' => \Tests\Log\Stub::class
                ],
            ],
        ],
        'handlerWithProcessor' => [
            'name'     => 'handlerWithProcessor',
            'handlers' => [
                [
                    'name'       => \Monolog\Handler\StreamHandler::class,
                    'stream'     => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log',
                    'level'      => 'debug',
                    'processors' => [
                        [
                            'processor' => \Monolog\Processor\WebProcessor::class
                        ],
                    ],
                ],
            ],
        ],
        'noFormatter'          => [
            'name'     => 'noFormatter',
            'handlers' => [
                [
                    'name'           => \Monolog\Handler\StreamHandler::class,
                    'stream'         => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log',
                    'level'          => 'debug',
                    'bubble'         => true,
                    'filePermission' => null,
                    'useLocking'     => false,
                    'processors'     => [],
                    'formatter'      => [
                        'name' => null
                    ]
                ],
            ],
        ],
        'wrongFormatter'       => [
            'name'     => 'wrongFormatter',
            'handlers' => [
                [
                    'name'           => \Monolog\Handler\StreamHandler::class,
                    'stream'         => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log',
                    'level'          => 'debug',
                    'bubble'         => true,
                    'filePermission' => null,
                    'useLocking'     => false,
                    'processors'     => [],
                    'formatter'      => [
                        'name' => \Tests\Log\Stub::class
                    ]
                ],
            ],
        ],
        'handlerWithFormatter' => [
            'name'     => 'handlerWithFormatter',
            'handlers' => [
                [
                    'name'      => \Monolog\Handler\StreamHandler::class,
                    'stream'    => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'app.log',
                    'level'     => 'debug',
                    'formatter' => [
                        'name' => \Monolog\Formatter\LineFormatter::class
                    ],
                ],
            ],
        ],
        'null'                 => [
            'handlers' => [
                'name'  => \Monolog\Handler\StreamHandler::class,
                'level' => 'debug'
            ],
        ]
    ]
];