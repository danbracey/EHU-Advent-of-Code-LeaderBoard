<?php
include __DIR__.'/vendor/autoload.php';
//Configure Environment Variables
$strJsonFileContents = file_get_contents("config.json");
// Convert to array
$config = json_decode($strJsonFileContents, true);

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Discord\WebSockets\Event;

$discord = new Discord([
    'token' => $config['token'],
]);

$discord->on('ready', function (Discord $discord) use ($config) {
    echo "Bot is ready!", PHP_EOL;

// Listen for messages.
    $discord->on(Event::INTERACTION_CREATE, function ( Interaction $interaction) use ($config) {
        if($interaction["data"]["name"] === "leaderboard") {
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'https://adventofcode.com/2022/leaderboard/private/view/' . $config['leaderboard_code'] . '.json', [
                    'headers' => [
                        'Cookie' => 'session=' . $config['session']
                    ]
                ]);
                $result = json_decode($response->getBody(), true);
                $results = "**Advent of Code Leaderboard at " . date('l jS \of F Y h:i A'). "**\n";

                $i = 0;
                foreach ($result["members"] as $k => $v) {
                    if ($i === 0) {
                        $results .= ":trophy: " . $v["name"] . " (" . $v["id"] . ") | Score: " . $v["local_score"] . " | :star:'s " . $v["stars"] . "\n";
                    } elseif ($i === 1) {
                        $results .= ":second_place: " . $v["name"] . " (" . $v["id"] . ") | Score: " . $v["local_score"] . " | :star:'s " . $v["stars"] . "\n";
                    } elseif ($i === 2) {
                        $results .= ":third_place: " . $v["name"] . " (" . $v["id"] . ") | Score: " . $v["local_score"] . " | :star:'s " . $v["stars"] . "\n";
                    } else {
                        $results .= $v["name"] . " (" . $v["id"] . ") | Score: " . $v["local_score"] . " | :star:'s " . $v["stars"] . "\n";
                    }

                    $i++;
                }

                try {
                    $interaction->respondWithMessage(MessageBuilder::new()
                        ->setContent($results)
                    );
                } catch (\Exception $exception) {
                    $interaction->respondWithMessage(MessageBuilder::new()
                        ->setContent(":x: There was a problem when trying to view the Leaderboards, please speak to a Developer")
                        ->_setFlags(1 << 6)
                    );
                }
            } catch(Exception $exception) {
                //var_dump($exception->getTrace());
                $interaction->respondWithMessage(MessageBuilder::new()
                    ->setContent("Unable to view Leaderboards, please try again later!")
                );
            }
        }
    });
});

$discord->run();