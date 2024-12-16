// assets/js/main.js

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.createElement('button');
    menuButton.className = 'menu-toggle';
    menuButton.innerHTML = '☰';
    document.querySelector('.navbar').prepend(menuButton);

    menuButton.addEventListener('click', () => {
        document.querySelector('.nav-links').classList.toggle('active');
    });
});

// Form validation
function validateLoginForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const errorDiv = document.querySelector('.error') || document.createElement('div');
    
    if (!username || !password) {
        errorDiv.className = 'error';
        errorDiv.textContent = 'Please fill in all fields';
        document.querySelector('.login-form').prepend(errorDiv);
        return false;
    }
    return true;
}

// Post loading with infinite scroll
let page = 1;
const loadMorePosts = () => {
    const postsContainer = document.querySelector('.posts-container');
    if (!postsContainer) return;

    fetch(`api/posts.php?page=${page}`)
        .then(response => response.json())
        .then(data => {
            data.posts.forEach(post => {
                const postElement = createPostElement(post);
                postsContainer.appendChild(postElement);
            });
            page++;
        });
};

function createPostElement(post) {
    const div = document.createElement('div');
    div.className = 'post-card';
    div.innerHTML = `
        <h3>${post.title}</h3>
        <p>${post.content.substring(0, 200)}...</p>
        <div class="post-meta">
            <span>By: ${post.author}</span>
            <span>${post.created_at}</span>
        </div>
        <div class="post-actions">
            <button onclick="likePost(${post.id})">Like</button>
            <button onclick="showComments(${post.id})">Comments</button>
        </div>
    `;
    return div;
}

// Infinite scroll implementation
window.addEventListener('scroll', () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
        loadMorePosts();
    }
});

// Material filter functionality
function filterMaterials() {
    const course = document.getElementById('course-select').value;
    const year = document.getElementById('year-select').value;
    
    fetch(`api/materials.php?course=${course}&year=${year}`)
        .then(response => response.json())
        .then(data => {
            const materialsContainer = document.querySelector('.materials-container');
            materialsContainer.innerHTML = '';
            data.materials.forEach(material => {
                materialsContainer.appendChild(createMaterialElement(material));
            });
        });
}

function createMaterialElement(material) {
    const div = document.createElement('div');
    div.className = 'material-card';
    div.innerHTML = `
        <h4>${material.title}</h4>
        <p>${material.course_type} - ${material.year_sem}</p>
        <a href="downloads/${material.file_path}" class="download-btn">Download</a>
    `;
    return div;
}

// Comment system
function showComments(postId) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="comments-container"></div>
            <textarea id="new-comment"></textarea>
            <button onclick="submitComment(${postId})">Add Comment</button>
        </div>
    `;
    document.body.appendChild(modal);
    loadComments(postId);
}

function loadComments(postId) {
    fetch(`api/comments.php?post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.querySelector('.comments-container');
            data.comments.forEach(comment => {
                container.appendChild(createCommentElement(comment));
            });
        });
}

function submitComment(postId) {
    const content = document.getElementById('new-comment').value;
    fetch('api/comments.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ post_id: postId, content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadComments(postId);
            document.getElementById('new-comment').value = '';
        }
    });
}