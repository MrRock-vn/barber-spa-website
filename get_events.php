<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/MrRock-vn/barber-spa-website/events");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, "PHP-Script");
$output = curl_exec($ch);
curl_close($ch);
$events = json_decode($output, true);
foreach ($events as $event) {
    if ($event['type'] === 'PushEvent' && $event['payload']['ref'] === 'refs/heads/main') {
        echo "Found push event!\n";
        echo "Before SHA: " . $event['payload']['before'] . "\n";
        echo "After SHA: " . $event['payload']['head'] . "\n";
        break;
    }
}
