<?php
// Configuration for database connection
// IMPORTANT: 'db' is the hostname, as defined by the service name in docker-compose.yml
$servername = "db";
$username = "user";
$password = "password";
$dbname = "voting_app";

// Initialize message variable
$message_html = '';

// Establish a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Note: If this fails, check your docker-compose.yml configuration and the 'db' container logs.
    die("Connection failed: " . $conn->connect_error);
}

// --- Function to safely display messages (now stores HTML string for the toast) ---
function display_message($message, $type = 'success') {
    global $message_html;
    
    // Tailwind classes for a fixed, bottom-centered, fading toast notification
    // It starts at opacity-0 and is made visible by the JavaScript below.
    $base_class = "fixed bottom-5 left-1/2 transform -translate-x-1/2 px-6 py-3 rounded-full shadow-xl z-50 text-white font-semibold transition-opacity duration-300 opacity-0";
    
    if ($type === 'success') {
        $color_class = "bg-green-600";
        $icon = "✅";
    } else {
        $color_class = "bg-red-600";
        $icon = "❌";
    }

    $message_html = "<div id='toast-message' class='$base_class $color_class'>$icon $message</div>";
}

// --- Vote Handling Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote_id'])) {
    $vote_id = filter_var($_POST['vote_id'], FILTER_VALIDATE_INT);

    if ($vote_id !== false && $vote_id > 0) {
        // CRITICAL LEARNING OBJECTIVE: ATOMIC UPDATE
        // Increments the count directly in the database to prevent race conditions.
        $sql = "UPDATE votes SET count = count + 1 WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $vote_id);
        
        if ($stmt->execute()) {
            display_message("Your vote has been successfully cast!");
        } else {
            display_message("Error casting vote: " . $stmt->error, 'error');
        }
        $stmt->close();
    } else {
        display_message("Invalid vote option selected.", 'error');
    }
}

// --- Data Retrieval Logic ---
$result = $conn->query("SELECT id, option_text, count FROM votes ORDER BY id ASC");
$options = [];
$total_votes = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
        $total_votes += $row['count'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevOps Voting App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f7f9fc; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 md:p-10 shadow-2xl rounded-xl w-full max-w-2xl">
        <header class="mb-8">
            <h1 class="text-4xl font-extrabold text-gray-900 border-b pb-3 mb-2">
                DevOps Container Voting Poll
            </h1>
            <p class="text-xl text-indigo-600 font-semibold">
                What is the most important component of a great DevOps pipeline?
            </p>
            <p class="text-sm text-gray-500 mt-1">
                Total Votes Cast: <span class="font-bold text-gray-700"><?= $total_votes ?></span>
            </p>
        </header>

        <form method="POST" action="index.php" class="space-y-4 mb-8">
            <?php foreach ($options as $option): ?>
                <div class="flex items-center p-4 border border-gray-200 rounded-lg transition duration-200 hover:bg-indigo-50">
                    <input id="option-<?= $option['id'] ?>" type="radio" name="vote_id" value="<?= $option['id'] ?>" required
                           class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500">
                    <label for="option-<?= $option['id'] ?>" class="ml-3 text-lg font-medium text-gray-800 w-full cursor-pointer">
                        <?= htmlspecialchars($option['option_text']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg 
                           shadow-md transition duration-300 ease-in-out transform hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:ring-opacity-50">
                🚀 Cast Your Atomic Vote!
            </button>
        </form>

        <!-- Display Results -->
        <h2 class="text-2xl font-bold text-gray-700 mb-4 border-t pt-4">Current Results</h2>
        <div class="space-y-3">
            <?php foreach ($options as $option): ?>
                <?php
                    $percentage = $total_votes > 0 ? round(($option['count'] / $total_votes) * 100) : 0;
                    $bar_width = $percentage > 0 ? $percentage : 1; // Ensure a small bar is visible even for 0%
                ?>
                <div class="flex flex-col">
                    <div class="flex justify-between text-sm font-medium text-gray-600 mb-1">
                        <span><?= htmlspecialchars($option['option_text']) ?></span>
                        <span><?= $option['count'] ?> Votes (<?= $percentage ?>%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-indigo-500 h-3 rounded-full transition-all duration-500" 
                             style="width: <?= $bar_width ?>%;">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <footer class="mt-8 pt-4 border-t text-xs text-gray-400 text-center">
            <p>Built with PHP/MySQL & Docker Compose for a hands-on DevOps learning experience.</p>
            <p>Key feature: Atomic Database Update (count = count + 1).</p>
        </footer>
    </div>

    <!-- Inject the message HTML here -->
    <?= $message_html ?>

    <!-- JavaScript for toast visibility -->
    <script>
        const toast = document.getElementById('toast-message');
        if (toast) {
            // Show the toast by setting opacity to 100
            setTimeout(() => {
                toast.classList.remove('opacity-0');
                toast.classList.add('opacity-100');
            }, 100); 

            // Hide the toast after 3 seconds by setting opacity to 0
            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0');
            }, 3000);

            // Remove the element completely after it fades out 
            setTimeout(() => {
                toast.remove();
            }, 3500);
        }
    </script>
</body>
</html>
