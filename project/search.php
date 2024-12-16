<?php
// search.php
<?php
require_once 'includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$query = isset($_GET['q']) ? $_GET['q'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
?>

<?php include 'includes/header.php'; ?>

<div class="search-page">
    <div class="search-header">
        <h1>Search Results</h1>
        <form class="search-form" method="GET">
            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                   placeholder="Search..." required>
            <select name="type">
                <option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All</option>
                <option value="posts" <?php echo $type == 'posts' ? 'selected' : ''; ?>>Posts</option>
                <option value="materials" <?php echo $type == 'materials' ? 'selected' : ''; ?>>Materials</option>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="search-results" id="searchResults"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const query = '<?php echo addslashes($query); ?>';
    const type = '<?php echo addslashes($type); ?>';
    
    if(query) {
        fetchResults(query, type);
    }
});

async function fetchResults(query, type) {
    try {
        const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}&type=${type}`);
        const data = await response.json();
        
        if(data.success) {
            displayResults(data.results, type);
        }
    } catch(error) {
        console.error('Search failed:', error);
    }
}

function displayResults(results, type) {
    const container = document.getElementById('searchResults');
    container.innerHTML = '';
    
    if(type === 'all') {
        // Display all types of results
        if(results.posts.length > 0) {
            container.innerHTML += `
                <div class="result-section">
                    <h2>Posts</h2>
                    ${createPostResults(results.posts)}
                </div>
            `;
        }
        
        if(results.materials.length > 0) {
            container.innerHTML += `
                <div class="result-section">
                    <h2>Materials</h2>
                    ${createMaterialResults(results.materials)}
                </div>
            `;
        }
        
        if(results.users.length > 0) {
            container.innerHTML += `
                <div class="result-section">
                    <h2>Users</h2>
                    ${createUserResults(results.users)}
                </div>
            `;
        }
    } else {
        // Display specific type results
        container.innerHTML = createResults(results, type);
    }
}

function createPostResults(posts) {
    return posts.map(post => `
        <div class="result-item">
            <h3><a href="post.php?id=${post.post_id}">${escapeHtml(post.title)}</a></h3>
            <p>${escapeHtml(post.content.substring(0, 200))}...</p>
            <div class="meta">
                By ${escapeHtml(post.full_name)} on ${formatDate(post.created_at)}
            </div>
        </div>
    `).join('');
}

function createMaterialResults(materials) {
    return materials.map(material => `
        <div class="result-item">
            <h3>${escapeHtml(material.title)}</h3>
            <div class="meta">
                Uploaded by ${escapeHtml(material.full_name)} on ${formatDate(material.created_at)}
            </div>
            <a href="download.php?id=${material.material_id}" class="download-btn">Download</a>
        </div>
    `).join('');
}

function createUserResults(users) {
    return users.map(user => `
        <div class="result-item">
            <h3><a href="profile.php?id=${user.user_id}">${escapeHtml(user.full_name)}</a></h3>
            <div class="meta">
                ${escapeHtml(user.department)}
            </div>
        </div>
    `).join('');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString();
}
</script>

<style>
.search-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.search-header {
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.search-form input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.result-section {
    margin-bottom: 2rem;
}

.result-item {
    background: #fff;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.meta {
    color: #666;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.download-btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 0.5rem;
}

.download-btn:hover {
    background: #0056b3;
}
</style>

<?php include 'includes/footer.php'; ?>