<?php

namespace app\commands;

use MCurl\Client;
use yii\console\Controller;

class TestController extends Controller
{
    /**
     * @param int $num
     * @param string $dst
     * @param $conf
     * @throws \Exception
     */
    public function actionIndex($num = 100, $dst = '127.0.0.1', $conf)
    {
        $data = json_decode(file_get_contents($conf), 1);

        while (true) {
            /** @var Client[] $clients */
            $clients = [];

            for ($i = 0; $i < $num; $i++) {
                $client = new Client();

                $statServ = $data[random_int(0, count($data) - 1)];

                /** @noinspection CurlSslServerSpoofingInspection */
                $params = [
                    CURLOPT_URL => $dst,
                    CURLOPT_CONNECTTIMEOUT => 1,
                    CURLOPT_TIMEOUT => 1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_INTERFACE => $statServ['serv'],
                    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
                ];

                $params[CURLOPT_POSTFIELDS] = $statServ['stat'] . ' ' . random_int(0, 100) . ' ' . time();

                $client->enableHeaders();
                $client->add($params);

                $clients[] = $client;
            }

            do {
                foreach ($clients as $key => $client) {
                    $client->run();

                    if ($client->has()) {
                        $result = $client->next();

                        if ($result->getErrorCode() === null) {
                            echo "client $key finished " . $result->getHttpCode() . PHP_EOL;
                        } else {
                            echo "client $key failed " . $result->getHttpCode() . PHP_EOL;
                        }

                        unset($clients[$key]);
                    }
                }
            } while (count($clients) > 0);
        }
    }
}
