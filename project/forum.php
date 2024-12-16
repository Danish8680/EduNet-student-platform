<?php
// forum.php

require_once 'includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDbConnection();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total questions count for pagination
$count_query = "SELECT COUNT(*) as total FROM posts WHERE title LIKE ?";
$stmt = $conn->prepare($count_query);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$total_posts = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);

// Get questions with user info and comment counts
$query = "SELECT p.*, u.full_name, u.user_id,
          (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) as comment_count
          FROM posts p
          JOIN users u ON p.user_id = u.user_id
          WHERE p.title LIKE ?
          ORDER BY p.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $search_param, $limit, $offset);
$stmt->execute();
$questions = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<div class="forum-container">
    <div class="forum-header">
        <h1>Open Forum</h1>
        <button onclick="showNewQuestionForm()" class="new-question-btn">Ask Question</button>
    </div>

    <div class="search-bar">
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search questions..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div id="question-form" class="question-form" style="display: none;">
        <h3>Ask a Question</h3>
        <form id="new-question-form">
            <div class="form-group">
                <label for="question-title">Title</label>
                <input type="text" id="question-title" name="title" required>
            </div>
            <div class="form-group">
                <label for="question-content">Description</label>
                <textarea id="question-content" name="content" required></textarea>
            </div>
            <button type="submit">Post Question</button>
            <button type="button" onclick="hideNewQuestionForm()">Cancel</button>
        </form>
    </div>

    <div class="questions-list">
        <?php if($questions->num_rows > 0): ?>
            <?php while($question = $questions->fetch_assoc()): ?>
                <div class="question-card">
                    <div class="question-stats">
                        <div class="stat">
                            <span class="number"><?php echo $question['comment_count']; ?></span>
                            <span class="label">comments</span>
                        </div>
                    </div>
                    
                    <div class="question-content">
                        <h3>
                            <a href="post.php?id=<?php echo $question['post_id']; ?>">
                                <?php echo htmlspecialchars($question['title']); ?>
                            </a>
                        </h3>
                        <div class="question-excerpt">
                            <?php echo htmlspecialchars(substr($question['content'], 0, 200)); ?>...
                        </div>
                        <div class="question-meta">
                            <span class="author">
                                Asked by <a href="profile.php?id=<?php echo $question['user_id']; ?>">
                                    <?php echo htmlspecialchars($question['full_name']); ?>
                                </a>
                            </span>
                            <span class="date">
                                <?php echo date('M d, Y at H:i', strtotime($question['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <div class="no-questions">
                <p>No questions found. Be the first to ask!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showNewQuestionForm() {
    document.getElementById('question-form').style.display = 'block';
}

function hideNewQuestionForm() {
    document.getElementById('question-form').style.display = 'none';
}

document.getElementById('new-question-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        title: document.getElementById('question-title').value,
        content: document.getElementById('question-content').value
    };

    fetch('api/create_post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(formData)
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