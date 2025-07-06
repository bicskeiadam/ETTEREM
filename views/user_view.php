<?php
session_start();
// Enable error reporting for debugging HTTP 500 errors (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Restaurant Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../styles/userstyle.css" />
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.scrollY > 50) {
                document.querySelector('.header').classList.add('visible');
                document.querySelector('.footer').classList.add('visible');
            }
        });
    </script>
</head>
<body>

<!-- Header -->
<header class="header text-white">
    <h1 class="headertext fs-5 m-0" id="section-title">Lapmesterek</h1>
    <div class="user-info">
        <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="User" width="24" height="24" />
    </div>
</header>
<div id="user-popup" class="user-popup hidden">
    <div class="user-popup-content">
        <?php
        if (isset($_SESSION['id_user'])) {
            $firstname = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : '';
            $lastname = isset($_SESSION['lastname']) ? $_SESSION['lastname'] : '';
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
            if (!$firstname || !$lastname || !$role) {
                require_once '../register/config.php';
                // Check for required config variables
                if (!isset($dsn) || !isset($pdoOptions) || !defined('PARAMS')) {
                    die('Database configuration variables are missing in config.php');
                }
                try {
                    $pdo = new PDO($dsn, PARAMS['USER'], PARAMS['PASS'], $pdoOptions);
                } catch (PDOException $e) {
                    die('Database connection failed: ' . $e->getMessage());
                }
                $stmt = $pdo->prepare("SELECT firstname, lastname, role FROM users WHERE id_user = ?");
                $stmt->execute([$_SESSION['id_user']]);
                $user = $stmt->fetch();
                if ($user) {
                    $firstname = $user['firstname'];
                    $lastname = $user['lastname'];
                    $role = $user['role'];
                    $_SESSION['firstname'] = $firstname;
                    $_SESSION['lastname'] = $lastname;
                    $_SESSION['role'] = $role;
                }
            }
            echo '<p id="welcome-text">Hello, ' . htmlspecialchars($firstname . ' ' . $lastname) . '!</p>';
            echo '<button id="logout-btn" onclick="location.href=\'../register/logout.php\'">Log out</button>';
            if ($role === 'staff') {
                echo '<button id="admin-btn" onclick="location.href=\'../views/staff_dashboard.php\'" style="margin-top:2px;">Staff</button>';
            }
            if ($role === 'admin') {
                echo '<button id="admin-btn" onclick="location.href=\'../views/admin_dashbord.php\'" style="margin-top:2px;">Admin</button>';
            }
        } else {
            echo '<p id="welcome-text">Hello, Guest!</p>';
            echo '<button id="login-btn" onclick="location.href=\'../register/index.php\'">Log in</button>';
        }
        ?>
    </div>
</div>

<!-- Main content -->
<main>
    <!-- Intro Section – Carousel -->
    <section id="home">
        <div id="introCarousel" class="carousel slide overflow-hidden position-relative" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- Slide 1: Welcome -->
                <div class="carousel-item active">
                    <div class="carousel-overlay">
                        <div class="carousel-overlay-content">
                            <h2 class="carousel-title">Welcome to Lapmesterek</h2>
                            <p class="carousel-subtitle">Serving exquisite food since 1999</p>
                            <button class="carousel-btn" onclick="location.href='#menu'">Explore our Dishes</button>
                        </div>
                    </div>
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Restaurant Interior">
                </div>

                <!-- Slide 2: Today's Special -->
                <div class="carousel-item">
                    <div class="carousel-overlay">
                        <div class="carousel-overlay-content">
                            <h2 class="carousel-title">Today's Best Rated Meal</h2>
                            <p class="carousel-subtitle">Grilled Salmon with Citrus Butter Sauce</p>
                            <button class="carousel-btn" onclick="location.href='#menu'">Order Now</button>
                        </div>
                    </div>
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Today's Special">
                </div>

                <!-- Slide 3: Chef's Recommendation -->
                <div class="carousel-item">
                    <div class="carousel-overlay">
                        <div class="carousel-overlay-content">
                            <h2 class="carousel-title">Chef's Recommendation</h2>
                            <p class="carousel-subtitle">Try our award-winning Hungarian dishes</p>
                            <button class="carousel-btn" onclick="location.href='#menu'">View Menu</button>
                        </div>
                    </div>
                    <img src="https://images.unsplash.com/photo-1533777857889-4be7c70b33f7?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Chef's Recommendation">
                </div>

                <!-- Slide 4: Weekend Special -->
                <div class="carousel-item">
                    <div class="carousel-overlay">
                        <div class="carousel-overlay-content">
                            <h2 class="carousel-title">Weekend Special Offer</h2>
                            <p class="carousel-subtitle">20% off on family dinners every Saturday</p>
                            <button class="carousel-btn" onclick="location.href='#reservation'">Reserve Now</button>
                        </div>
                    </div>
                    <img src="https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?q=80&w=1742&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Weekend Special">
                </div>

                <!-- Slide 5: Customer Favorite -->
                <div class="carousel-item">
                    <div class="carousel-overlay">
                        <div class="carousel-overlay-content">
                            <h2 class="carousel-title">Customer Favorite</h2>
                            <p class="carousel-subtitle">Our signature Tiramisu - loved by everyone</p>
                            <button class="carousel-btn" onclick="location.href='#menu'">Try It Today</button>
                        </div>
                    </div>
                    <img src="https://images.unsplash.com/photo-1533777857889-4be7c70b33f7?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Customer Favorite">
                </div>
            </div>
            <div class="custom-indicators d-flex justify-content-center mt-3">
                <button type="button" data-bs-target="#introCarousel" data-bs-slide-to="0" class="indicator active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#introCarousel" data-bs-slide-to="1" class="indicator" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#introCarousel" data-bs-slide-to="2" class="indicator" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#introCarousel" data-bs-slide-to="3" class="indicator" aria-label="Slide 4"></button>
                <button type="button" data-bs-target="#introCarousel" data-bs-slide-to="4" class="indicator" aria-label="Slide 5"></button>
            </div>
        </div>
    </section>

    <!-- Indicators below Carousel -->



    <!-- Popular Dishes -->
    <section id="menu" class="mb-4 container">
        <h2 class="text-center mb-4 popular-dishes-title">Popular Dishes</h2>

        <div class="row g-4">
            <?php
            require_once '../includes/menu_functions.php';
            $popularDishes = getPopularDishes(8);

            foreach ($popularDishes as $dish) {
                $imageUrl = !empty($dish['image_url']) ? $dish['image_url'] : 'https://cdn.pixabay.com/photo/2021/05/01/22/01/meat-6222139_1280.jpg';
                $formattedPrice = '€' . number_format($dish['price'] / 100, 2);
                $description = !empty($dish['description']) ? $dish['description'] : $dish['category_name'];
                if (strlen($description) > 60) {
                    $description = substr($description, 0, 57) . '...';
                }

                echo '
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="dish-box p-3 rounded shadow h-100 d-flex flex-column">
                    <div class="dish-img-container mb-3">
                        <img src="' . $imageUrl . '" alt="' . htmlspecialchars($dish['name']) . '" class="dish-img"/>
                        <div class="dish-price">' . $formattedPrice . '</div>
                    </div>
                    <div class="dish-content flex-grow-1 d-flex flex-column">
                        <h5 class="dish-title">' . htmlspecialchars($dish['name']) . '</h5>
                        <p class="dish-category mb-1">' . htmlspecialchars($dish['category_name']) . '</p>
                        <p class="dish-description flex-grow-1">' . htmlspecialchars($description) . '</p>
                        <button class="order-btn mt-auto" onclick="window.location.href=\'../reservation/order.php?id=' . (int)$dish['id'] . '\'">Order Now</button>
                    </div>
                </div>
            </div>';
            }
            ?>
        </div>
    </section>

    <!-- Customer Reviews -->
    <section id="reviews" class="mb-5 container">
        <h2 class="text-center mb-4 popular-dishes-title">Customer Reviews</h2>

        <div class="row g-4">
            <?php
            require_once '../includes/menu_functions.php';
            $customerReviews = getCustomerReviews(4); // Get 4 customer reviews

            foreach ($customerReviews as $review) {
                // Generate star rating HTML
                $starsHtml = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $review['rating']) {
                        $starsHtml .= '<span class="star filled">★</span>';
                    } else {
                        $starsHtml .= '<span class="star">☆</span>';
                    }
                }

                echo '
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="review-box p-3 rounded shadow h-100">
                    <div class="review-img-container mb-3">
                        <img src="' . $review['image_url'] . '" alt="' . htmlspecialchars($review['name']) . '" class="review-img"/>
                    </div>
                    <div class="review-content">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="review-name mb-0">' . htmlspecialchars($review['name']) . '</h5>
                            <div class="review-stars">' . $starsHtml . '</div>
                        </div>
                        <p class="review-title">' . htmlspecialchars($review['title']) . '</p>
                        <p class="review-date">' . htmlspecialchars($review['date']) . '</p>
                        <p class="review-description">' . htmlspecialchars($review['description']) . '</p>
                    </div>
                </div>
            </div>';
            }
            ?>
        </div>
    </section>
    <!-- Reservation Carousel -->
    <section id="reservation">
        <div id="reservationCarousel" class="carousel slide overflow-hidden position-relative" data-bs-ride="carousel">
            <!-- Static text overlay that doesn't change -->
            <div class="static-carousel-overlay">
                <div class="carousel-overlay-content">
                    <h2 class="carousel-title">Like what you see?</h2>
                    <p class="carousel-subtitle">Reserve your table now!</p>
                    <a href="../reservation/asztalfoglalas.php" class="carousel-btn">Reserve Your Table!</a>
                </div>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Reservation">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Reservation Background 2">
                </div>
                <div class="carousel-item">
                    <img src="https://images.unsplash.com/photo-1533777857889-4be7c70b33f7?q=80&w=1740&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="Reservation Background 3">
                </div>
            </div>
        </div>
    </section>

</main>



<!-- Footer -->
<footer class="footer text-center text-white">
    <div class="d-flex justify-content-around align-items-center w-100">
        <a href="#introCarousel" class="text-white text-decoration-none">🏠 Home</a>
        <?php if (isset($_SESSION['id_user'])): ?>
            <a href="../reservation/user_reservations.php" class="a-res-btn text-white text-decoration-none">📅 Reservations</a>
        <?php else: ?>
            <a id="res-btn" class="a-res-btn text-white text-decoration-none" href="javascript:void(0);">📅 Reservations</a>
        <?php endif; ?>
        <a id="contact-btn" class="a-contact-btn text-white text-decoration-none">📞 Contact</a>
    </div>
    <div id="contact-popup" class="contact-popup hidden">
        <p class="contact-phone">📞 +3819669193222</p>
    </div>

    <div id="res-popup" class="res-popup hidden">
        <p class="res-text">Register or Log in first!</p>
    </div>
</footer>

<!-- Functions -->
<script src="../js/functions.js"> </script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
