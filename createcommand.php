<?php
include __DIR__.'/vendor/autoload.php';
//Configure Environment Variables
$strJsonFileContents = file_get_contents("config.json");
// Convert to array
$config = json_decode($strJsonFileContents, true);

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;

$discord = new Discord([
    'token' => $config['token'],
]);

$discord->on('ready', function (Discord $discord) use ($config) {
    $command = new Command($discord, [
        'name' => 'leaderboard',
        'guild_id' => $config['guild_id'],
        'description' => "View the leaderboard for this year's Advent of Code",
    ]);

    $discord->application->commands->save($command);
});

$discord->run();