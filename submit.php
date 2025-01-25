<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Borrowing Receipt</title>
    <style>
        .receipt-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 12px;
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-container {
            max-width: 400px;
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ff0000;
            border-radius: 8px;
            background-color: #ffe6e6;
            color: #ff0000;
            font-size: 14px;
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .receipt-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 15px;
        }

        .label {
            color: #555;
            font-weight: bold;
        }

        .value {
            color: #000;
        }

        .footer {
            font-size: 13px;
            color: #888;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Collecting form data
    $full_name = htmlspecialchars($_POST['full_name']);
    $user_id = htmlspecialchars($_POST['user_id']);
    $user_mail = htmlspecialchars($_POST['user_mail']);
    $book_title = htmlspecialchars($_POST['book_title']);
    $borrow_date = htmlspecialchars($_POST['borrow_date']);
    $token = htmlspecialchars($_POST['token']);
    $return_date = htmlspecialchars($_POST['return_date']);

    // Load available tokens
    $token_json = 'token.json'; // Path to the available token file
    $used_json = 'used.json';  // Path to the used token file

    if (!file_exists($token_json)) {
        die("Token file not found.");
    }

    $token_data = json_decode(file_get_contents($token_json), true);
    $available_tokens = $token_data['token'];

    // Load used tokens
    $used_tokens = [];
    if (file_exists($used_json)) {
        $used_data = json_decode(file_get_contents($used_json), true);
        $used_tokens = $used_data['used_token'] ?? [];
    }

    // Validation rules
    if (!preg_match("/^[A-Za-z\s]+$/", $full_name)) {
        $errors[] = "Invalid student name! Only letters, spaces, and periods are allowed.";
    }

    if (!preg_match("/^\d{2}-\d{5}-\d{1}$/", $user_id)) {
        $errors[] = "Invalid student ID format! It should match 'XX-XXXXX-X'.";
    }

    if (!preg_match("/^\d{2}-\d{5}-\d{1}@student\.aiub\.edu$/", $user_mail)) {
        $errors[] = "Invalid email format! It should be 'XX-XXXXX-X@student.aiub.edu'.";
    }

    if ($book_title === "None") {
        $errors[] = "No book selected. Please choose a book title.";
    }

    if (strtotime($borrow_date) > strtotime($return_date)) {
        $errors[] = "Borrow date cannot be later than the return date.";
    }

    // Calculate borrowing period in days
    $borrowing_period = (strtotime($return_date) - strtotime($borrow_date)) / (60 * 60 * 24);

    // Token validation only for periods greater than 10 days
    if ($borrowing_period > 10) {
        if (empty($token)) {
            $errors[] = "Token is required for borrowing periods longer than 10 days.";
        } elseif (in_array($token, $used_tokens)) {
            $errors[] = "The token has already been used.";
        } elseif (!in_array($token, $available_tokens)) {
            $errors[] = "Invalid token. Token not available.";
        }
    }

    // Cookie handling for duplicate borrow
    $cookie_name = strtolower(preg_replace('/[^a-z0-9]/i', '', $book_title));
    $book_already_borrowed = isset($_COOKIE[$cookie_name]);

    // Show errors or receipt
    if (!empty($errors)) {
        echo "<div class='error-container'><h3>Validation Errors</h3><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul></div>";
    } elseif ($book_already_borrowed) {
        // If book is already borrowed
        echo "<div class='error-container'>";
        echo "<p>The book <strong>$book_title</strong> has already been borrowed.</p>";
        echo "</div>";
    } else {
        // Set cookie and display receipt
        setcookie($cookie_name, time(), time() + 60); // Cookie set for 1 minute

        // Update token JSON
        if (($key = array_search($token, $available_tokens)) !== false) {
            unset($available_tokens[$key]);
            file_put_contents($token_json, json_encode(['token' => array_values($available_tokens)], JSON_PRETTY_PRINT));
        }

        // Add token to used tokens
        $used_tokens[] = $token;
        file_put_contents($used_json, json_encode(['used_token' => $used_tokens], JSON_PRETTY_PRINT));

        echo "<div class='receipt-container'>";
        echo "<h2>Borrowing Receipt</h2>";
        echo "<div class='receipt-detail'><span class='label'>Full Name:</span> <span class='value'>$full_name</span></div>";
        echo "<div class='receipt-detail'><span class='label'>ID:</span> <span class='value'>$user_id</span></div>";
        echo "<div class='receipt-detail'><span class='label'>Email:</span> <span class='value'>$user_mail</span></div>";
        echo "<div class='receipt-detail'><span class='label'>Book Title:</span> <span class='value'>$book_title</span></div>";
        echo "<div class='receipt-detail'><span class='label'>Borrow Date:</span> <span class='value'>$borrow_date</span></div>";
        echo "<div class='receipt-detail'><span class='label'>Token:</span> <span class='value'>$token</span></div>";
        echo "<div class='receipt-detail'><span class='label'>Return Date:</span> <span class='value'>$return_date</span></div>";
        echo "<div class='footer'>Thank you for using our service!</div>";
        echo "</div>";
    }
}
?>
</body>
</html>
