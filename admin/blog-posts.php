<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$message = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $id = (int)$_POST['delete_post_id'];
    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    $message = '<div class="alert alert-danger">Blog post deleted successfully!</div>';
}

// Handle form submissions (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post'])) {
    $title = sanitize_input($_POST['title']);
    $category = sanitize_input($_POST['category']);
    $excerpt = sanitize_input($_POST['excerpt']);
    $content = $_POST['content'];
    $tags = sanitize_input($_POST['tags']);
    $status = sanitize_input($_POST['status']);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $author_id = $_SESSION['user_id'];
    $featured_image = '';

    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = '../assets/images/blog/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'assets/images/blog/' . $file_name;
        }
    }

    if (!empty($_POST['post_id'])) {
        $post_id = (int)$_POST['post_id'];
        $sql = "UPDATE blog_posts SET title=?, slug=?, content=?, excerpt=?, category=?, status=?, tags=?";
        $params = [$title, $slug, $content, $excerpt, $category, $status, $tags];

        if ($featured_image) {
            $sql .= ", featured_image=?";
            $params[] = $featured_image;
        }

        $sql .= " WHERE id=?";
        $params[] = $post_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $message = '<div class="alert alert-success">Post updated successfully!</div>';
    } else {
        $stmt = $pdo->prepare("INSERT INTO blog_posts (title, author_id, slug, content, excerpt, featured_image, category, status, tags)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author_id, $slug, $content, $excerpt, $featured_image, $category, $status, $tags]);

        $message = '<div class="alert alert-success">Blog post added successfully!</div>';
    }
}

$stmt = $pdo->query("SELECT blog_posts.*, users.full_name as author_name
                     FROM blog_posts
                     JOIN users ON blog_posts.author_id = users.id
                     ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manage Blog Posts";
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/sidebar.php';
?>

<div class="main-content">
    <div class="header">
        <h1>Blog Posts</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPostModal">
            <i class="fas fa-plus"></i> Add New Post
        </button>
    </div>

    <?php if (!empty($message)) echo $message; ?>

    <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Views</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['category']); ?></td>
                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                <td><span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($post['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                <td><?php echo (int)$post['view_count']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='editPost(<?php echo json_encode($post); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this post?');">
                                        <input type="hidden" name="delete_post_id" value="<?php echo $post['id']; ?>">
                                        <button class="btn btn-sm btn-danger" type="submit">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addPostModal" tabindex="-1" aria-labelledby="addPostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPostModalLabel">Add New Blog Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="post_id">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select</option>
                                <option value="Faith">Faith</option>
                                <option value="Community">Community</option>
                                <option value="Prayer">Prayer</option>
                                <option value="Bible Study">Bible Study</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" rows="10" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input type="text" name="tags" class="form-control" placeholder="faith, community">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Featured Image</label>
                    <input type="file" name="featured_image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="save_post" class="btn btn-primary">Save Post</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPost(post) {
    const modal = new bootstrap.Modal(document.getElementById('addPostModal'));
    document.getElementById('addPostModalLabel').innerText = 'Edit Blog Post';

    document.querySelector('[name="post_id"]').value = post.id;
    document.querySelector('[name="title"]').value = post.title;
    document.querySelector('[name="category"]').value = post.category;
    document.querySelector('[name="excerpt"]').value = post.excerpt;
    document.querySelector('[name="content"]').value = post.content;
    document.querySelector('[name="status"]').value = post.status;
    document.querySelector('[name="tags"]').value = post.tags;

    modal.show();
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
