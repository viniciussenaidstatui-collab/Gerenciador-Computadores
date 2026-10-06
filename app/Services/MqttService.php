<?php

namespace App\Services;

use App\Models\Rfid;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;


class MqttService
{

public function receberDados() {

$host = env('MQTT_HOST');
$port = env('MQTT_PORT');

$username = env('MQTT_USERNAME');
$password = env('MQTT_PASSWORD');

$topic = env('MQTT_TOPIC');


$mqtt = new MqttClient($host, $port, 'Aula1-Cliente' . uniqid());

$settings = (new ConnectionSettings)
    
    ->setUsername($username)
    ->setPassword($password)
    ->setUseTls(true);

$mqtt->connect($settings, true);
echo('Laravel Conectado ao HiveMQ Broker com sucesso!');

$mqtt->subscribe($topic, function (string $topic, string $message) {

echo("Mensagem recebida no tópico \n");
echo $message . "\n";

$dados = json_decode($message, true);

Rfid::create([

    'rfid_code' => $dados['rfid_code'],
    'usuario_id' => $dados['usuario_id'],
    'retirado' => $dados['retirado'],
    'devolvido' => $dados['devolvido'],
    
    ]);

    echo("Dados salvos no banco de dados com sucesso!\n");

},0);

    echo("Aguardando mensagens no tópico: " . $topic . "\n");

    $mqtt->loop(true);

}};