<?php
// post.php
require_once 'includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = (int)$_GET['id'];
$conn = getDbConnection();

// Get post details
$query = "SELECT p.*, u.full_name, u.user_id 
          FROM posts p 
          JOIN users u ON p.user_id = u.user_id 
          WHERE p.post_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if(!$post) {
    header('Location: index.php');
    exit();
}

// Get comments
$comment_query = "SELECT c.*, u.full_name, u.user_id 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.user_id 
                 WHERE c.post_id = ? 
                 ORDER BY c.created_at ASC";
$stmt = $conn->prepare($comment_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<div class="post-detail-container">
    <div class="post-full">
        <div class="post-header">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="author">
                    <?php echo htmlspecialchars($post['full_name']); ?> 
                    (<?php echo htmlspecialchars($post['user_id']); ?>)
                </a>
                <span class="date"><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
            </div>
        </div>

        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <?php if($_SESSION['user_id'] == $post['user_id'] || $_SESSION['user_type'] == 'admin'): ?>
            <div class="post-actions">
                <button onclick="deletePost(<?php echo $post_id; ?>)" class="delete-btn">Delete Post</button>
            </div>
        <?php endif; ?>

        <div class="comments-section">
            <h3>Comments</h3>
            
            <form id="comment-form" class="comment-form">
                <textarea name="content" placeholder="Write a comment..." required></textarea>
                <button type="submit">Post Comment</button>
            </form>

            <div class="comments-list">
                <?php while($comment = $comments->fetch_assoc()): ?>
                    <div class="comment" id="comment-<?php echo $comment['comment_id']; ?>">
                        <div class="comment-header">
                            <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="comment-author">
                                <?php echo htmlspecialchars($comment['full_name']); ?>
                            </a>
                            <span class="comment-date">
                                <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?>
                            </span>
                        </div>
                        
                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>

                        <?php if($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['user_type'] == 'admin'): ?>
                            <div class="comment-actions">
                                <button onclick="deleteComment(<?php echo $comment['comment_id']; ?>)" class="delete-btn">
                                    Delete
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<script>
function deleteComment(commentId) {
    if(confirm('Are you sure you want to delete this comment?')) {
        fetch('api/delete_comment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({comment_id: commentId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById(`comment-${commentId}`).remove();
            }
        });
    }
}

document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const content = this.content.value;
    
    fetch('api/add_comment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            post_id: <?php echo $post_id; ?>,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>