<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit2'])) {
    // Retrieve form data
    $isbn = $_POST['isbn'];
    $name = $_POST['name'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    $category = $_POST['category'];

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO books (isbn, name, author, quantity, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $isbn, $name, $author, $quantity, $category);

    if ($stmt->execute()) {
        echo "<script>alert('New book added successfully!');</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all books from the table
$sql = "SELECT * FROM books";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book Borrowing Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="allbg">
        <div class="top-image">22-47819-2</div>
    <!-- <img src="ID.png" alt="Top Image" class="top-image"> -->

    <div class="background">


        <div class="leftbox">
        <h4>USED TOKEN</h4>
    <div id="used-tokens-list">
    <?php
            // Load used tokens
            $used_tokens = [];
            if (file_exists('used.json')) {
                $json_data = file_get_contents('used.json');
                $used_tokens = json_decode($json_data, true); // Decode JSON into an array
            }
 
            if (!empty($used_tokens['used_token'])) {
                foreach ($used_tokens['used_token'] as $used_token) {
                    echo '<div>' . htmlspecialchars($used_token) . '</div>';
                }
            } else {
                echo '<div>No used tokens available</div>';
            }
            ?>
    </div>
        </div>
        <div class="middle">
            <div class="first">
            <div class="box1">
                 <h3 style="text-align: center;">Book List</h3>
        <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ISBN</th>
                    <th>Name</th>
                    <th>Author</th>
                    <th>Quantity</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['isbn']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['author']}</td>
                                <td>{$row['quantity']}</td>
                                <td>{$row['category']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No books found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Search -->
    <div class="box0">
    <h3>Search Book</h3>
    <form action="" method="post" id="search-form">
        <input type="text" name="search_book" id="search-book" placeholder="Enter Book Name" required>
        <button type="submit" name="search_submit">Search</button>
    </form>

    <div class="search-results">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ISBN</th>
                    <th>Name</th>
                    <th>Author</th>
                    <th>Quantity</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
                    $search_query = htmlspecialchars(trim($_POST['search_book']));

                    // Search the database for the book name
                    $search_sql = "SELECT * FROM books WHERE name LIKE ?";
                    $stmt = $conn->prepare($search_sql);
                    $search_pattern = "%" . $search_query . "%";
                    $stmt->bind_param("s", $search_pattern);
                    $stmt->execute();
                    $search_result = $stmt->get_result();

                    if ($search_result->num_rows > 0) {
                        while ($row = $search_result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['isbn']}</td>
                                    <td>{$row['name']}</td>
                                    <td>{$row['author']}</td>
                                    <td>{$row['quantity']}</td>
                                    <td>{$row['category']}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No books found.</td></tr>";
                    }

                    $stmt->close();
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

     <!--  -->

                <div class="box11"> 
                    <h3 style="text-align: center;" class="heading">Add Book</h3>
                    <form action="" method="post">
                        <!-- ISBN -->
                        <label for="isbn">ISBN:</label>
                        <input style="margin-left: 38px;" type="text" id="isbn" name="isbn" required>
                        <br><br>
                
                        <!-- Name -->
                        <label for="name">Book Name:</label>
                        <input type="text" id="name" name="name" required>
                        <br><br>
                
                        <!-- Author -->
                        <label for="author">Author:</label>
                        <input style="margin-left: 33px;" type="text" id="author" name="author" required>
                        <br><br>
                
                        <!-- Quantity -->
                        <label for="quantity">Quantity:</label>
                        <input style="margin-left: 28px;" type="number" id="quantity" name="quantity" min="1" required>
                        <br><br>
                
                        <!-- Category -->
                        <label for="category">Category:</label>
                        <input style="margin-left: 28px;" type="text" id="category" name="category" required>
                        <br><br>
                
                        <button type="submit" name = "submit2">Add Book</button>
                    </form>

                </div>
                <!--  -->
                
                <!--  -->
                <div class="box12">
                    <h3>Update And Delete</h3>
                <form action=""  method="POST">
        <!-- ID Field (Mandatory for both Update and Delete) -->
        <label for="book_id">Book ID (Mandatory):</label>
        <input type="text" name="book_id" id="book_id" placeholder="Enter Book ID" required>
        <br><br>

        <!-- Editable Fields for Update -->
        <label for="book_title">Book Title:</label>
        <input type="text" name="book_title" id="book_title" placeholder="Enter New Book Title">
        <br><br>

        <label for="author_name">Author Name:</label>
        <input type="text" name="author_name" id="author_name" placeholder="Enter New Author Name">
        <br><br>

        <label for="isbn_number">ISBN Number:</label>
        <input type="text" name="isbn_number" id="isbn_number" placeholder="Enter New ISBN Number">
        <br><br>

        <!-- Buttons for Update and Delete -->
        <button type="submit" name="action" value="update">Update</button>
        <button type="submit" name="action" value="delete">Delete</button>
    </form>
                </div>

                <?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "library_db");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $action = $_POST['action'];
    $book_id = htmlspecialchars(trim($_POST['book_id']));

    if (empty($book_id)) {
        echo "<p style='color: red; text-align: center;'>Book ID is mandatory for both update and delete actions.</p>";
    } else {
        if ($action === "update") {
            // Collect updated fields
            $book_title = !empty($_POST['book_title']) ? htmlspecialchars(trim($_POST['book_title'])) : null;
            $author_name = !empty($_POST['author_name']) ? htmlspecialchars(trim($_POST['author_name'])) : null;
            $isbn_number = !empty($_POST['isbn_number']) ? htmlspecialchars(trim($_POST['isbn_number'])) : null;

            // Build the SQL query dynamically
            $update_fields = [];
            if ($book_title) {
                $update_fields[] = "name='$book_title'";
            }
            if ($author_name) {
                $update_fields[] = "author='$author_name'";
            }
            if ($isbn_number) {
                $update_fields[] = "isbn='$isbn_number'";
            }

            if (!empty($update_fields)) {
                $update_query = "UPDATE books SET " . implode(", ", $update_fields) . " WHERE id='$book_id'";
                if ($conn->query($update_query) === TRUE) {
                    echo "<p style='color: green; text-align: center;'>Book details updated successfully!</p>";
                } else {
                    echo "<p style='color: red; text-align: center;'>Error updating book details: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color: orange; text-align: center;'>No fields to update. Please fill at least one field.</p>";
            }
        } elseif ($action === "delete") {
            // Delete query
            $delete_query = "DELETE FROM books WHERE id='$book_id'";
            if ($conn->query($delete_query) === TRUE) {
                echo "<p style='color: green; text-align: center;'>Book deleted successfully!</p>";
            } else {
                echo "<p style='color: red; text-align: center;'>Error deleting book: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: red; text-align: center;'>Invalid action.</p>";
        }
    }

    // Close connection
    $conn->close();
}
?>

            </div>
            <div class="second">

                <div class="box2">
                        <div class="image-holder">
                            <a href="#">
                                <img src="./images/Adv1.jfif" alt="img-1">
                            </a>
                            <div class="desc">
                                Price : 200
                                <br>
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                <button>Buy</button>
                            </div>
                        </div>
                </div>
                <div class="box2">
                    <div class="image-holder">
                        <a href="#">
                            <img src="./images/Adv2.jfif" alt="img-1">
                        </a>
                        <div class="desc">
                            Price : 300
                            <br>
                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                            <button>Buy</button>
                        </div>
                    </div>
                </div>
                <div class="box2">
                    <div class="image-holder">
                        <a href="#">
                            <img src="./images/Adv3.jfif" alt="img-1">
                        </a>
                        <div class="desc">
                            Price : 250
                            <br>
                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                            <button>Buy</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="third">
                <div class="box3">
                    
                </div>
            </div>
            <div class="fourth">
                <div class="form-container">
                    <p class="title">Welcome</p>
                    <form class="form" action="submit.php" method="POST">
                        <!-- Full Name -->
                        <input type="text" name="full_name" class="input-field" placeholder="Full Name" required autofocus>
                        <br>
            
                        <!-- ID -->
                        <input type="text" 
                             name="user_id" 
                             class="input-field" 
                             placeholder="ID" 
                             required>
                        <br>

                        <input type="email" 
                            name="user_mail" 
                            class="input-field" 
                            placeholder="Email"   
                            required>
                        <br>
            
                        <!-- Book Title (Dropdown) -->
                        <select name="book_title" class="input-field" required>
                            <option value="" disabled selected>Select Book Title</option>
                                 <?php
                                 // Fetch book titles from the database
                                 $sql = "SELECT name FROM books";
                                 $result = $conn->query($sql);
                                    // Check if there are books in the database
                                    if ($result->num_rows > 0) {
                                    // Output data of each row
                                    while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['name'] . "'>" . $row['name'] . "</option>";
                                     }
                                     } else {
                                         echo "<option value=''>No books available</option>";
                                  }
                                ?>
                        </select>
                        <br>
            
                        <label class="label" for="borrow_date">Borrow Date</label>
                        <input type="date" id="borrow_date" name="borrow_date" class="input-field" required>
                        <br>

                        <input type="text" name="token" id="token-field" class="input-field" placeholder="Token">
                        <br>
                        
                        <label class="label" for="return_date">Return Date</label>
                        <input type="date" name="return_date" class="input-field" required>
                        <br>
            
                        <button type="submit" class="form-btn">Submit</button>
                    </form>
                    
                </div>
            

                <div class="box5">
                <h4>Available Token</h4>
                        <div id="token-list">
                            <?php
                            // Load tokens from the JSON file
                            $tokens = [];
                            if (file_exists('token.json')) {
                                $json_data = file_get_contents('token.json');
                                $tokens = json_decode($json_data, true); // Decode JSON data into an associative array
                            }
                            ?>
                            <?php if (!empty($tokens['token'])): ?>
                                <?php foreach ($tokens['token'] as $token): ?>
                                    <div><?php echo htmlspecialchars($token); ?></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div>No tokens available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <!-- </div>
                </div>
            </div> -->
        </div>
        
        <div class="rightbox"></div>
    </div>
    </div>

    
    <script>
    window.onload = function () {
        window.scrollTo(0, 0);
    };
</script>
</body>
</html>

<?php
$conn->close();
?>