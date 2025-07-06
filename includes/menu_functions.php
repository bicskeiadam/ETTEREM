<?php
require_once '../register/config.php';
require_once '../register/functions_def.php';

/**
 * Gets popular dishes from the database
 *
 * @param int $limit Number of dishes to return
 * @return array Array of dish data
 */
function getPopularDishes($limit = 8) {
    global $pdo;

    // In a real application, you might determine popularity based on orders or ratings
    // For now, we'll just get random dishes from the menu_items table
    $query = "SELECT mi.id, mi.name, mi.description, mi.price, mi.image_url, mc.name as category_name 
              FROM menu_items mi
              JOIN menu_categories mc ON mi.category_id = mc.id
              WHERE mi.is_available = 1
              ORDER BY RAND()
              LIMIT :limit";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Gets customer reviews
 *
 * @param int $limit Number of reviews to return
 * @return array Array of review data
 */
function getCustomerReviews($limit = 4) {
    // Since we don't have a reviews table in the database yet,
    // we'll return hardcoded reviews with ratings and detailed descriptions
    $reviews = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'rating' => 5,
            'title' => 'Exceptional dining experience!',
            'description' => 'The food was absolutely delicious, especially the grilled salmon. The atmosphere was perfect for our anniversary dinner. The staff went above and beyond to make our evening special.',
            'date' => '2025-05-15',
            'image_url' => 'https://randomuser.me/api/portraits/men/32.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Jane Smith',
            'rating' => 4,
            'title' => 'Great food and service',
            'description' => 'I really enjoyed the pasta dishes and the wine selection was excellent. The service was attentive without being intrusive. Will definitely be coming back soon!',
            'date' => '2025-05-10',
            'image_url' => 'https://randomuser.me/api/portraits/women/44.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Michael Johnson',
            'rating' => 5,
            'title' => 'Best restaurant in town!',
            'description' => 'We tried the chef\'s special and it was outstanding. The flavors were perfectly balanced and the presentation was beautiful. The dessert menu is also worth exploring!',
            'date' => '2025-05-05',
            'image_url' => 'https://randomuser.me/api/portraits/men/22.jpg'
        ],
        [
            'id' => 4,
            'name' => 'Emily Wilson',
            'rating' => 4,
            'title' => 'Lovely atmosphere',
            'description' => 'The ambiance of this restaurant is so charming and cozy. I loved the decor and the background music. The food was delicious too, especially the vegetarian options.',
            'date' => '2025-04-28',
            'image_url' => 'https://randomuser.me/api/portraits/women/28.jpg'
        ],
        [
            'id' => 5,
            'name' => 'David Brown',
            'rating' => 5,
            'title' => 'Exceptional service',
            'description' => 'The staff here is incredibly knowledgeable and friendly. They made excellent recommendations for both food and wine pairings. The tiramisu was the best I\'ve ever had!',
            'date' => '2025-04-20',
            'image_url' => 'https://randomuser.me/api/portraits/men/46.jpg'
        ]
    ];

    // Return only the requested number of reviews
    return array_slice($reviews, 0, $limit);
}