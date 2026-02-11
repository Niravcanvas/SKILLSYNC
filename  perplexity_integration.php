use SoftCreatR\PerplexityAI\Client;

function getPerplexityResponse($prompt) {
    $client = new Client('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');
    $response = $client->ask($prompt);
    return $response->getAnswer();
}