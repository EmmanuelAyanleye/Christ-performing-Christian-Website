<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Christ performing Christian Centre</title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : 'Join Christ performing Christian Centre - A vibrant Christian community in Nigeria. Experience worship, grow in faith, and connect with others.'; ?>">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/images/church_logo.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0b2067;
            --secondary-color: #f2db37;
            --accent-color: #059669;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }

        /* Navigation Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background-color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: 600;
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
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
            color:#fff;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(251, 191, 36, 0.3);
        }
        @media (max-width: 992px) {
            .filter-buttons {
                display: flex !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
                gap: 0.5rem !important;
            }
            .filter-btn{
                font-size: 10px;
            }

            .watch-btn{
                width: 100% !important;
            }

            .page-header h1 {
                font-size: 1.8rem !important;
            }
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2.5rem !important;
        }

        @media (max-width: 575.98px) {
            .page-header h1 {
                font-size: 1.5rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php require_once __DIR__ . '/navigation.php'; ?>