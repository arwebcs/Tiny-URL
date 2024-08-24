<?php

// Database connection details
$host = 'localhost';
$db = 'tinyurl_db';
$user = 'root';
$pass = '';

// Connect to MySQL database
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to generate a unique short code
function generateShortCode($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $shortCode = '';

    for ($i = 0; $i < $length; $i++) {
        $shortCode .= $characters[rand(0, $charactersLength - 1)];
    }

    return $shortCode;
}

// Function to create a tiny URL
function createTinyUrl($url)
{
    global $conn;

    // Check if the URL already exists in the database
    $query = "SELECT short_code FROM urls WHERE original_url = '" . mysqli_real_escape_string($conn, $url) . "' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['short_code'];
    }

    // Generate a new unique short code
    $shortCode = generateShortCode();

    // Insert the original URL and short code into the database
    $query = "INSERT INTO urls (original_url, short_code) VALUES ('" . mysqli_real_escape_string($conn, $url) . "', '$shortCode')";
    mysqli_query($conn, $query);

    return $shortCode;
}

// Function to retrieve the original URL from the short code
function getOriginalUrl($shortCode)
{
    global $conn;

    $query = "SELECT original_url FROM urls WHERE short_code = '" . mysqli_real_escape_string($conn, $shortCode) . "' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        
        $row = mysqli_fetch_assoc($result);
        return $row['original_url'];
    } else {
        return false;
    }
}

// Example usage
if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $shortCode = createTinyUrl($url);
    echo "Short URL: http://localhost/short_url/$shortCode";
}

if (isset($_GET['code'])) {
    $originalUrl = getOriginalUrl($_GET['code']);
    if ($originalUrl) {
        header("Location: $originalUrl");
        exit();
    } else {
        echo "Invalid URL";
    }
}
?>

<!-- HTML Form to Create Tiny URL -->
<form method="post" action="">
    <input type="text" name="url" placeholder="Enter URL" required>
    <button type="submit">Create Tiny URL</button>
</form>
