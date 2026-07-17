<?php

/**
 * Seed the ChiLove blog with the starter content from the asset pack.
 * Run:  php seed.php
 */

require __DIR__ . '/core/bootstrap.php';

use ChiLove\Core\Database;

$db = Database::instance();

// Fresh start (dev only).
$db->query('SET FOREIGN_KEY_CHECKS = 0');
foreach (['chi_post_terms', 'chi_posts', 'chi_terms', 'chi_users', 'chi_subscribers'] as $t) {
    $db->query("TRUNCATE TABLE $t");
}
$db->query('SET FOREIGN_KEY_CHECKS = 1');

// Author
$db->query("INSERT INTO chi_users (display_name, email) VALUES (?, ?)", ['Mia Carter', 'mia@chilove.test']);
$authorId = $db->lastInsertId();

// Categories
$categories = [
    'Care Tips' => 'care-tips',
    'Training'  => 'training',
    'Lifestyle' => 'lifestyle',
    'Health'    => 'health',
    'Gallery'   => 'gallery',
];
$termId = [];
foreach ($categories as $name => $slug) {
    $db->query("INSERT INTO chi_terms (name, slug, taxonomy) VALUES (?, ?, 'category')", [$name, $slug]);
    $termId[$slug] = $db->lastInsertId();
}

// Posts: [title, slug, category, excerpt, read_time, days_ago, content]
$posts = [
    ['Chihuahua Care 101: Everything New Owners Need to Know', 'chihuahua-care-101', 'care-tips',
     'Bringing home a Chihuahua? Here is your complete guide to feeding, training, warmth, playtime, and keeping your tiny companion happy and healthy.', 7, 1,
     "Chihuahuas may be tiny, but they have huge personalities and a few specific needs every new owner should know about.\n\nStart with warmth, a consistent feeding routine, gentle socialization, and plenty of patience. Small, frequent meals keep their energy steady, and a cozy bed away from drafts keeps them comfortable.\n\nWith a little structure and a lot of love, your Chihuahua will settle in fast and become the most devoted little shadow you have ever had."],

    ['10 Signs Your Chihuahua Absolutely Adores You', '10-signs-your-chihuahua-adores-you', 'lifestyle',
     'From the velcro-dog follow to the happy tail wiggle, here are the unmistakable ways your Chihuahua says "I love you".', 5, 3,
     "Chihuahuas are famously devoted, and they show love in dozens of tiny ways.\n\nWatch for the velcro-dog follow from room to room, the slow tail wag when you walk in, leaning into your lap, and those big eyes locking onto yours.\n\nIf your Chihuahua does these things, you are officially their favorite human in the whole world."],

    ['Best Winter Clothes for Chihuahuas', 'best-winter-clothes-for-chihuahuas', 'lifestyle',
     'Chihuahuas get cold fast. Here are the sweaters, jackets, and blankets that actually keep tiny dogs warm and comfortable.', 4, 5,
     "Chihuahuas get cold fast thanks to their small size and thin coats.\n\nA snug sweater, a padded jacket for walks, and a warm blanket at home make a huge difference. Look for soft, stretchy fabrics that cover the chest and back without restricting those little legs."],

    ['Chihuahua Training Tips That Actually Work', 'chihuahua-training-tips-that-work', 'training',
     'Smart, eager, and a little stubborn — here is how to train your Chihuahua with patience, treats, and consistency.', 6, 7,
     "Chihuahuas are smart and eager, but they respond best to gentle, consistent training.\n\nKeep sessions short and upbeat, reward generously with tiny treats, and never rely on scolding.\n\nPatience and routine turn a stubborn streak into happy cooperation."],

    ['Common Health Issues in Chihuahuas', 'common-health-issues-in-chihuahuas', 'health',
     'Knowing what to watch for helps you catch problems early. The most common Chihuahua health concerns, explained simply.', 8, 9,
     "Knowing the common Chihuahua health concerns helps you catch problems early.\n\nWatch for dental issues, luxating patella, low blood sugar in puppies, and tracheal sensitivity.\n\nRegular vet checkups and a good diet keep most issues manageable."],

    ['Fun Activities to Do with Your Chihuahua', 'fun-activities-with-your-chihuahua', 'lifestyle',
     'Tiny dogs still need stimulation. Sniff-walks, puzzle feeders, and cozy games that tire out your Chihuahua.', 5, 11,
     "Tiny dogs still need stimulation, and Chihuahuas love a good adventure.\n\nTry short sniff-walks, puzzle feeders, gentle fetch indoors, and cozy training games.\n\nMental enrichment tires them out just as well as exercise does."],

    ['How to Potty Train Your Chihuahua the Easy Way', 'how-to-potty-train-your-chihuahua', 'training',
     'Consistency beats everything. A calm, simple routine for potty training your Chihuahua without the frustration.', 6, 13,
     "Potty training a Chihuahua takes consistency more than anything else.\n\nSet a regular schedule, reward every success immediately, and watch for the little pre-potty signs.\n\nAccidents happen, so clean up calmly and just keep going."],

    ['What to Feed a Chihuahua: Simple Beginner Guide', 'what-to-feed-a-chihuahua', 'care-tips',
     'Small meals, the right food, and steady routines. A beginner-friendly guide to feeding your Chihuahua well.', 5, 15,
     "Feeding a Chihuahua well starts with small, high-quality meals.\n\nChoose a food formulated for small breeds, split it into a few portions a day to keep blood sugar steady, and go easy on treats.\n\nFresh water and a predictable routine do the rest."],

    ['How to Keep Your Chihuahua Warm in Cold Weather', 'keep-your-chihuahua-warm', 'care-tips',
     'When the temperature drops, your Chihuahua needs extra help. Cozy tips for a warm, happy tiny dog all winter.', 4, 17,
     "When temperatures drop, your Chihuahua needs extra help staying warm.\n\nLayer a sweater indoors, warm up their bed away from drafts, and keep winter walks short.\n\nA heated pad or a sunny window spot becomes their favorite place all season."],

    ['Chihuahua Personality: Why Tiny Dogs Act So Brave', 'chihuahua-personality-brave', 'health',
     'That big-dog attitude in a tiny body? Here is what is really behind your Chihuahua bold, loyal personality.', 6, 20,
     "Chihuahuas pack enormous courage into a tiny frame.\n\nTheir bold, loyal personality comes from deep attachment to their people.\n\nUnderstanding that bravado is really devotion helps you raise a confident, well-socialized little dog."],
];

foreach ($posts as $p) {
    [$title, $slug, $cat, $excerpt, $readTime, $daysAgo, $content] = $p;
    $db->query(
        "INSERT INTO chi_posts (author_id, title, slug, excerpt, content, read_time, status, published_at)
         VALUES (?, ?, ?, ?, ?, ?, 'publish', DATE_SUB(NOW(), INTERVAL ? DAY))",
        [$authorId, $title, $slug, $excerpt, $content, $readTime, $daysAgo]
    );
    $postId = $db->lastInsertId();
    $db->query("INSERT INTO chi_post_terms (post_id, term_id) VALUES (?, ?)", [$postId, $termId[$cat]]);
}

echo 'Seeded ' . count($posts) . ' posts across ' . count($categories) . " categories.\n";
