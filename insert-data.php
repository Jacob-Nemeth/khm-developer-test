<?php
session_start();

// Validate the request method
$is_post_request = $_SERVER['REQUEST_METHOD'] === 'POST';

// index.php submits only the tv-show field, so check for that instead of a non-existent submit value.
$is_expected_submission_action = isset($_POST['tv-show']);

// Validate the tv-show input: non-empty, alphanumeric, and free of HTML/SQL injection attempts.
$has_valid_data = !empty($_POST['tv-show']) && ctype_alnum($_POST['tv-show']) && !preg_match('/[< >]/', $_POST['tv-show']);

$valid = $is_post_request && $is_expected_submission_action && $has_valid_data;

if (!$valid) {
    // Indicate to frontend that the input was invalid and redirect back to the form.
    $_SESSION['error'] = 'Please enter a valid show name using only letters and numbers.';
    header('Location: index.php');
    exit;
}

$task_four_completed = true;

if ($task_four_completed && file_exists('rest-api.php')) {
    require_once 'rest-api.php';
}

/**
 * Save the validated show name to the database.
 * Table schema for reference:
 * CREATE TABLE `tv_shows` (`name` VARCHAR(20) PRIMARY KEY UNIQUE NOT NULL, `count` INT NOT NULL DEFAULT 0);
 */

$pdo = new PDO('sqlite:tv_shows.db');

try {
    // First check whether the show already exists in the database to decide whether to insert a new record or update the existing count.
    $statement = $pdo->query('SELECT `count` FROM `tv_shows` WHERE `name` = ' . $pdo->quote($show_name));
    $existing_show = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$existing_show) {
        // No existing record found, insert a new one with count = 1.
        $task_four_completed = true;
        $insert = $pdo->prepare('INSERT INTO `tv_shows` (`name`, `count`) VALUES (:name, 1)');
        $insert->execute(['name' => $show_name]);
    } else {
        // Existing record found, increment the count.
        $update = $pdo->prepare('UPDATE `tv_shows` SET `count` = `count` + 1 WHERE `name` = :name');
        $update->execute(['name' => $show_name]);
    }
} catch (\Exception $e) {
    // If an error occurs, inform the user and reload the page.
    $pdo = null;
    $_SESSION['error'] = 'An error occurred while saving your submission. Please try again.';
    header('Location: index.php');
    exit;
}

// Submission count displays were moved to index.php
$_SESSION['success'] = 'Your vote has been recorded. Thank you!';
header('Location: index.php');
exit;