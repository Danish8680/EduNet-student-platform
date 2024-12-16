<?php
// profile.php
require_once 'includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];
$conn = getDbConnection();

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(!$user) {
    header('Location: index.php');
    exit();
}

// Get user's posts
$posts_query = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($posts_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$posts = $stmt->get_result();

// Get user's stats
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM posts WHERE user_id = ?) as total_posts,
    (SELECT COUNT(*) FROM comments WHERE user_id = ?) as total_comments";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("ss", $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>

<div class="profile-container">
    <div class="profile-header">
        <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
        <div class="user-id"><?php echo htmlspecialchars($user['user_id']); ?></div>
        <?php if($_SESSION['user_type'] == 'admin' && $user['user_id'] != $_SESSION['user_id']): ?>
            <button onclick="deleteUser('<?php echo $user['user_id']; ?>')" class="delete-user-btn">Delete User</button>
        <?php endif; ?>
    </div>

    <div class="profile-content">
        <div class="user-info">
            <h3>User Information</h3>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($user['department']); ?></p>
            <p><strong>Year:</strong> <?php echo htmlspecialchars($user['year_of_study']); ?></p>
            <p><strong>Member since:</strong> <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
        </div>

        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo $stats['total_posts']; ?></span>
                <span class="stat-label">Posts</span>
            </div>
            <div class="stat-box">
                <span class="stat-number"><?php echo $stats['total_comments']; ?></span>
                <span class="stat-label">Comments</span>
            </div>
        </div>

        <div class="user-description">
            <h3>About</h3>
            <p><?php echo nl2br(htmlspecialchars($user['description'])); ?></p>
        </div>

        <div class="user-posts">
            <h3>Recent Posts</h3>
            <?php if($posts->num_rows > 0): ?>
                <?php while($post = $posts->fetch_assoc()): ?>
                    <div class="post-card">
                        <h4><a href="post.php?id=<?php echo $post['post_id']; ?>">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a></h4>
                        <div class="post-preview">
                            <?php echo htmlspecialchars(substr($post['content'], 0, 150)); ?>...
                        </div>
                        <div class="post-meta">
                            Posted on <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteUser(userId) {
    if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch('api/delete_user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: userId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                window.location.href = 'index.php';
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>