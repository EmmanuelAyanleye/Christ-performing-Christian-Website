<?php
require_once '../includes/config.php';
$page_title = "404 Not Found";
$page_description = "The page you are looking for does not exist.";
include '../includes/header.php';
?>
<style>
    .error-404 {
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        background: var(--bg-light, #f9fafb);
    }
    .error-404 h1 {
        font-size: 6rem;
        font-weight: 900;
        color: var(--primary-color, #1e3a8a);
        margin-bottom: 1rem;
    }
    .error-404 p {
        font-size: 1.5rem;
        color: var(--text-dark, #1f2937);
        margin-bottom: 2rem;
    }

    /* Footer */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer h5 {
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: var(--secondary-color);
        }

        .social-icons a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--secondary-color);
            color: var(--primary-color);
            text-align: center;
            line-height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(251, 191, 36, 0.3);
        }
</style>
<section class="error-404">
    <h1>404</h1>
    <p>Sorry, the page you are looking for could not be found.</p>
    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-primary">Go Home</a>
</section>
<?php include '../includes/footer.php'; ?>