<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Validate the submitted show exists using the TVmaze API.
 *
 * This file is required conditionally from insert-data.php — if it is present,
 * the show will be verified against the TVmaze API before being saved to the database.
 *
 * @TODO: Using the GuzzleHttp\Client instantiated below, make a GET request to:
 *      https://api.tvmaze.com/search/shows?q={tv-show}
 *
 *      - Decode the JSON response and check whether any results were returned.
 *      - If no results are found, redirect the user back to the form with a clear error message.
 *      - Catch \GuzzleHttp\Exception\RequestException to handle network or unexpected response failures.
 *      - Configure a sensible timeout on the client (e.g. 5 seconds).
 *
 * Guzzle docs: https://docs.guzzlephp.org/en/stable/quickstart.html
 * TVmaze API docs: https://www.tvmaze.com/api#show-search
 */
$http = new Client(['timeout' => 5, 'verify' => false]);

$tvmaze_query = 'https://api.tvmaze.com/search/shows?q=' . urlencode($_POST['tv-show']);

try {
    $response = $http->get($tvmaze_query);
    $results = json_decode($response->getBody(), true);
    
    // If the API returns a 404 or an empty array, the show was not found.
    if ($response->getStatusCode() == 404 || empty($results)) {
        $_SESSION['error'] = 'TV show not found. Please check the title and try again.';
        header('Location: index.php');
        exit;
    }

    $show_name = $results[0]['show']['name'] ?? $_POST['tv-show'];
} catch (RequestException $e) {
    // Report the error to the user, prompt them to retry.
    $_SESSION['error'] = 'Unable to verify TV show at this time. Please try again later.';
    header('Location: index.php');
    exit;
}

