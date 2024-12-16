<?php
// materials.php
require_once 'includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDbConnection();

$course_type = isset($_GET['course']) ? $_GET['course'] : '';
$year_sem = isset($_GET['year']) ? $_GET['year'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where = [];
if($course_type) $where[] = "course_type = '" . $conn->real_escape_string($course_type) . "'";
if($year_sem) $where[] = "year_sem = '" . $conn->real_escape_string($year_sem) . "'";
if($search) $where[] = "(title LIKE '%" . $conn->real_escape_string($search) . "%')";

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT m.*, u.full_name 
          FROM materials m 
          JOIN users u ON m.uploaded_by = u.user_id 
          $where_clause 
          ORDER BY m.created_at DESC";
$result = $conn->query($query);
?>

<?php include 'includes/header.php'; ?>

<div class="materials-container">
    <h1>Educational Materials</h1>

    <?php if($_SESSION['user_type'] == 'admin'): ?>
        <div class="upload-section">
            <h3>Upload New Material</h3>
            <form action="api/upload_material.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                
                <div class="form-group">
                    <label>Course Type</label>
                    <select name="course_type" required>
                        <option value="">Select Course</option>
                        <option value="BSc">B.Sc.</option>
                        <option value="BCA">BCA</option>
                        <option value="MSc">M.Sc.</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Year/Semester</label>
                    <select name="year_sem" required>
                        <option value="">Select Year</option>
                        <option value="1st_year">1st Year</option>
                        <option value="2nd_year">2nd Year</option>
                        <option value="3rd_year">3rd Year</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>File</label>
                    <input type="file" name="material_file" required accept=".pdf,.doc,.docx,.ppt,.pptx">
                </div>
                
                <button type="submit">Upload Material</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="materials-filter">
        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search materials..." value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="course">
                <option value="">All Courses</option>
                <option value="BSc" <?php echo $course_type == 'BSc' ? 'selected' : ''; ?>>B.Sc.</option>
                <option value="BCA" <?php echo $course_type == 'BCA' ? 'selected' : ''; ?>>BCA</option>
                <option value="MSc" <?php echo $course_type == 'MSc' ? 'selected' : ''; ?>>M.Sc.</option>
            </select>
            
            <select name="year">
                <option value="">All Years</option>
                <option value="1st_year" <?php echo $year_sem == '1st_year' ? 'selected' : ''; ?>>1st Year</option>
                <option value="2nd_year" <?php echo $year_sem == '2nd_year' ? 'selected' : ''; ?>>2nd Year</option>
                <option value="3rd_year" <?php echo $year_sem == '3rd_year' ? 'selected' : ''; ?>>3rd Year</option>
            </select>
            
            <button type="submit">Filter</button>
        </form>
    </div>

    <div class="materials-grid">
        <?php if($result->num_rows > 0): ?>
            <?php while($material = $result->fetch_assoc()): ?>
                <div class="material-card">
                    <div class="material-info">
                        <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                        <p class="course-info">
                            <?php echo htmlspecialchars($material['course_type']); ?> - 
                            <?php echo htmlspecialchars($material['year_sem']); ?>
                        </p>
                        <p class="upload-info">
                            Uploaded by <?php echo htmlspecialchars($material['full_name']); ?><br>
                            on <?php echo date('M d, Y', strtotime($material['created_at'])); ?>
                        </p>
                    </div>
                    
                    <div class="material-actions">
                        <a href="download.php?id=<?php echo $material['material_id']; ?>" class="download-btn">
                            Download
                        </a>
                        <?php if($_SESSION['user_type'] == 'admin'): ?>
                            <button onclick="deleteMaterial(<?php echo $material['material_id']; ?>)" class="delete-btn">
                                Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-materials">No materials found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteMaterial(materialId) {
    if(confirm('Are you sure you want to delete this material?')) {
        fetch('api/delete_material.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({material_id: materialId})
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