<?php
// create-post.php

require_once 'includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<?php include 'includes/header.php'; ?>

<div class="create-post-container">
    <h1>Create New Post</h1>
    
    <div class="alert" id="messageBox" style="display: none;"></div>
    
    <form id="createPostForm" class="post-form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required 
                   class="form-control" maxlength="255">
        </div>

        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" required 
                      class="form-control" rows="10"></textarea>
        </div>

        <div class="form-group">
            <label for="image">Image (Optional)</label>
            <input type="file" id="image" name="image" 
                   accept="image/*" class="form-control-file">
            <div id="imagePreview"></div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn btn-primary">Create Post</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = `<img src="${e.target.result}" class="img-preview">`;
        }
        reader.readAsDataURL(file);
    }
});

document.getElementById('createPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('title', document.getElementById('title').value);
    formData.append('content', document.getElementById('content').value);
    
    const imageFile = document.getElementById('image').files[0];
    if(imageFile) {
        formData.append('image', imageFile);
    }

    try {
        const response = await fetch('api/create_post.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if(data.success) {
            window.location.href = `post.php?id=${data.post_id}`;
        } else {
            showMessage(data.message || 'Error creating post', 'error');
        }
    } catch(error) {
        showMessage('Error creating post', 'error');
    }
});

function showMessage(message, type) {
    const messageBox = document.getElementById('messageBox');
    messageBox.textContent = message;
    messageBox.className = `alert alert-${type}`;
    messageBox.style.display = 'block';
}
</script>

<style>
.create-post-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 1rem;
}

.post-form {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.img-preview {
    max-width: 300px;
    margin-top: 1rem;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}

.alert-error {
    background: #fee;
    color: #c00;
    border: 1px solid #fcc;
}

.alert-success {
    background: #efe;
    color: #0c0;
    border: 1px solid #cfc;
}

.button-group {
    display: flex;
    gap: 1rem;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    text-decoration: none;
}
</style>

<?php include 'includes/footer.php'; ?>