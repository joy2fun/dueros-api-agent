<?php

namespace App;

use Baidu\Duer\Botsdk\Card\TextCard;
use Baidu\Duer\Botsdk\Bot;
use GuzzleHttp\Client;

class AgentBot extends Bot {
    public function __construct($postData = []) {
        parent::__construct();

        $this->addLaunchHandler(function () {
            $this->waitAnswer();
            return [
                'card' => new TextCard('应用已开启'),
                'outputSpeech' => '应用已开启',
            ];
        });

        $this->addSessionEndedHandler(function () {
            return [
                'card' => new TextCard('应用已关闭'),
                'outputSpeech' => '应用已关闭',
            ];
        });

        $this->registerIntentHandlers();
    }

    protected function createIntentHandler($intent, $slots) {
        $api = getenv('API_ENDPOINT');
        return function () use ($intent, $slots, $api) {
            $this->waitAnswer();
            try {
                $client = new Client;
                $response = $client->post($api, [
                    'intent' => $intent,
                    'slots' => $slots,
                ]);
                $content = $response->getBody();
                $data = json_decode($content, true);

                if ($data && isset($data['output'])) {
                    return [
                        'card' => new TextCard($data['card'] ?? $data['output']),
                        'outputSpeech' => $data['output'],
                    ];
                } else {
                    return [
                        'card' => new TextCard(
                            json_encode(
                                [$intent, $slots],
                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                            )
                        ),
                        'outputSpeech' => '接口返回格式有问题',
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'card' => new TextCard(substr($e->getMessage(), 0, 100) . '...'),
                    'outputSpeech' => '接口异常',
                ];
            }
        };
    }

    protected function registerIntentHandlers() {
        $intents = require ROOT_PATH . '/intents.php';

        foreach((array) $intents as $intent => $slots) {
            $data = [];
            foreach ($slots as $slot) {
                $data[$slot] = $this->getSlot($slot);
            }
            $this->addIntentHandler($intent, $this->createIntentHandler($intent, $data));
        }
    }
}
