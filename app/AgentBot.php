<?php

namespace App;

use Baidu\Duer\Botsdk\Card\TextCard;
use Baidu\Duer\Botsdk\Bot;
use Exception;
use GuzzleHttp\Client;

class AgentBot extends Bot {

    protected $intent = '';
    protected $slots = [];

    public function __construct($postData = []) {
        parent::__construct();
        $this->parseRequest();

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

        if ($this->intent) {
            $this->addIntentHandler($this->intent, [$this, 'intentHandler']);
        }
    }

    protected function parseRequest() {
        $input = file_get_contents("php://input");
        if (empty($input)) return;

        $data = json_decode($input, true);
        if (! $data) return;

        if (! isset($data['request']['intents'])) return;
        try {
            $intent = current($data['request']['intents']);
            $this->intent = $intent['name'] ?? '';
            $this->slots = array_map(function($item) {
                return $item['value'];
            }, $intent['slots']);
        } catch (Exception $e) {
            // ignore
        }
    }

    protected function intentHandler() {
        $api = getenv('API_ENDPOINT');
        $this->waitAnswer();
        try {
            $client = new Client;
            $response = $client->post($api, [
                'form_params' => [
                    'intent' => $this->intent,
                    'slots' => $this->slots,
                ]
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
                            [$this->intent, $this->slots],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        )
                    ),
                    'outputSpeech' => '接口返回格式有问题',
                ];
            }
        } catch (Exception $e) {
            return [
                'card' => new TextCard(substr($e->getMessage(), 0, 100) . '...'),
                'outputSpeech' => '接口异常',
            ];
        }
    }
}
