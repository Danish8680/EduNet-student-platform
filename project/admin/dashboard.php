<?php
// admin/dashboard.php
require_once '../includes/functions.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$conn = getDbConnection();

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE user_type != 'admin') as total_users,
    (SELECT COUNT(*) FROM posts) as total_posts,
    (SELECT COUNT(*) FROM comments) as total_comments,
    (SELECT COUNT(*) FROM materials) as total_materials";
$stats = $conn->query($stats_query)->fetch_assoc();

// Get recent users
$users_query = "SELECT * FROM users WHERE user_type != 'admin' ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($users_query);

// Get recent posts
$posts_query = "SELECT p.*, u.full_name FROM posts p 
                JOIN users u ON p.user_id = u.user_id 
                ORDER BY p.created_at DESC LIMIT 5";
$recent_posts = $conn->query($posts_query);
?>

<?php include '../includes/header.php'; ?>

<div class="admin-dashboard">
    <h1>Admin Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Users</h3>
            <span class="stat-number"><?php echo $stats['total_users']; ?></span>
        </div>
        <div class="stat-card">
            <h3>Posts</h3>
            <span class="stat-number"><?php echo $stats['total_posts']; ?></span>
        </div>
        <div class="stat-card">
            <h3>Comments</h3>
            <span class="stat-number"><?php echo $stats['total_comments']; ?></span>
        </div>
        <div class="stat-card">
            <h3>Materials</h3>
            <span class="stat-number"><?php echo $stats['total_materials']; ?></span>
        </div>
    </div>

    <div class="admin-sections">
        <section class="users-section">
            <h2>Recent Users</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $recent_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['department']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <button onclick="deleteUser('<?php echo $user['user_id']; ?>')" class="delete-btn">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="users.php" class="view-all">View All Users</a>
        </section>

        <section class="posts-section">
            <h2>Recent Posts</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($post = $recent_posts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td><?php echo htmlspecialchars($post['full_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <button onclick="deletePost(<?php echo $post['post_id']; ?>)" class="delete-btn">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="posts.php" class="view-all">View All Posts</a>
        </section>
    </div>
</div>

<script>
function deleteUser(userId) {
    if(confirm('Are you sure you want to delete this user?')) {
        fetch('../api/delete_user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: userId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) location.reload();
        });
    }
}

function deletePost(postId) {
    if(confirm('Are you sure you want to delete this post?')) {
        fetch('../api/delete_post.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({post_id: postId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) location.reload();
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>