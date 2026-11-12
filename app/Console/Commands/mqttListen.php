<?php

namespace App\Console\Commands;

use App\Services\MqttService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:mqtt-listen')]
#[Description('Command description')]
class mqttListen extends Command
{

    protected $signature = 'app:mqtt-listen';
    protected $description = 'Escuta Mensagens MQTT do HiveMQ';

    public function handle()
    {
    
    $mqttService = new MqttService();
    $mqttService->receberDados();
    return Command::SUCCESS;

    }
}
