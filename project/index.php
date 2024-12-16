<?php
// index.php
require_once 'includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDbConnection();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if($search) {
    $where = "WHERE p.title LIKE '%$search%' OR p.content LIKE '%$search%'";
}

$query = "SELECT p.*, u.full_name, u.user_id, 
          (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) as comment_count 
          FROM posts p 
          JOIN users u ON p.user_id = u.user_id 
          $where
          ORDER BY p.created_at DESC 
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

$total_query = "SELECT COUNT(*) as total FROM posts p $where";
$total_result = $conn->query($total_query);
$total_posts = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);
?>

<?php include 'includes/header.php'; ?>

<div class="main-container">
    <div class="search-bar">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="posts-container">
        <h2>Recent Posts</h2>
        <?php if($result->num_rows > 0): ?>
            <?php while($post = $result->fetch_assoc()): ?>
                <div class="post-card" data-post-id="<?php echo $post['post_id']; ?>">
                    <div class="post-header">
                        <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="user-link">
                            <?php echo htmlspecialchars($post['full_name']); ?> (<?php echo htmlspecialchars($post['user_id']); ?>)
                        </a>
                        <span class="post-date"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                    </div>
                    
                    <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 300))); ?>
                        <?php if(strlen($post['content']) > 300): ?>
                            <a href="post.php?id=<?php echo $post['post_id']; ?>" class="read-more">...Read More</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-footer">
                        <button class="comment-btn" onclick="showComments(<?php echo $post['post_id']; ?>)">
                            Comments (<?php echo $post['comment_count']; ?>)
                        </button>
                        <?php if($_SESSION['user_id'] == $post['user_id'] || $_SESSION['user_type'] == 'admin'): ?>
                            <button class="delete-btn" onclick="deletePost(<?php echo $post['post_id']; ?>)">Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?page=<?php echo ($page-1); ?>&search=<?php echo urlencode($search); ?>">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?page=<?php echo ($page+1); ?>&search=<?php echo urlencode($search); ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-posts">
                <p>No posts found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deletePost(postId) {
    if(confirm('Are you sure you want to delete this post?')) {
        fetch('api/delete_post.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({post_id: postId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>