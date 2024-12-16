<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edunet Student Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="index.php">Edunet</a>
        </div>
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="nav-links">
                <a href="create-post.php">Create Post</a>
                <a href="forum.php">Open Forum</a>
                <a href="materials.php">Materials</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        <?php endif; ?>
    </nav>