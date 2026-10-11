<?php
header('Content-Type: application/json');
require('../config/autoload.php');

$dao = new DataAccess();

// Read input JSON or POST parameters
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

$userMsg = trim($data['message'] ?? $_POST['message'] ?? '');
$selectedMood = trim($data['mood'] ?? $_POST['mood'] ?? '');

if (empty($userMsg) && empty($selectedMood)) {
    echo json_encode([
        'status' => 'error',
        'reply' => 'Please tell me your current mood or what kind of book you are looking for!',
        'books' => []
    ]);
    exit();
}

$inputQuery = strtolower(trim($userMsg . ' ' . $selectedMood));

// Define Mood Keywords mapping
$moodMap = [
    'peace' => [
        'keywords' => ['peace', 'peaceful', 'calm', 'relax', 'soothing', 'tranquil', 'healing', 'mindful', 'gentle', 'quiet', 'nature', 'life'],
        'title' => '🧘 Peace & Relaxation',
        'intro' => "I hear you! When you need tranquility and peace of mind, a gentle, engrossing story is perfect to help you unwind."
    ],
    'romantic' => [
        'keywords' => ['romance', 'romantic', 'love', 'heart', 'heartwarming', 'sweet', 'couple', 'passion', 'relationship'],
        'title' => '💖 Romantic & Heartwarming',
        'intro' => "Love is in the air! Here are some captivating romantic reads that will warm your heart and bring a smile to your face."
    ],
    'thrill' => [
        'keywords' => ['thrill', 'thriller', 'suspense', 'mystery', 'crime', 'dark', 'danger', 'detective', 'secret', 'investigate', 'death', 'puzzle'],
        'title' => '🕵️ Mystery & Suspense',
        'intro' => "In the mood for edge-of-your-seat excitement? These mystery and thriller titles will keep you hooked line after line!"
    ],
    'fantasy' => [
        'keywords' => ['fantasy', 'magic', 'supernatural', 'beast', 'wood', 'forest', 'fairy', 'adventure', 'realm', 'world'],
        'title' => '🧙 Magical & Fantasy',
        'intro' => "Ready to escape into extraordinary realms? Step into magical worlds full of wonder, mystery, and enchantment."
    ],
    'drama' => [
        'keywords' => ['drama', 'emotional', 'tragedy', 'sad', 'cry', 'tears', 'bond', 'family', 'brother', 'amends', 'deep'],
        'title' => '🎭 Emotional & Drama',
        'intro' => "For times when you want a deeply moving narrative full of emotion, personal growth, and human connections:"
    ],
    'action' => [
        'keywords' => ['action', 'fight', 'power', 'hero', 'battle', 'journey', 'space', 'scifi', 'technology', 'future'],
        'title' => '⚡ Action & Sci-Fi',
        'intro' => "Gear up for futuristic tech, epic journeys, and high-energy excitement! Check out these dynamic titles:"
    ],
    'manga' => [
        'keywords' => ['manga', 'anime', 'japan', 'comic', 'light yagami', 'death note', 'your name', 'shinkai'],
        'title' => '📚 Manga & Illustrated',
        'intro' => "Explore iconic manga storytelling with captivating artwork and legendary plot twists!"
    ]
];

// Determine matched primary mood
$matchedMoodKey = null;
$highestKeywordScore = 0;

foreach ($moodMap as $key => $info) {
    $score = 0;
    foreach ($info['keywords'] as $kw) {
        if (strpos($inputQuery, $kw) !== false) {
            $score++;
        }
    }
    if ($score > $highestKeywordScore) {
        $highestKeywordScore = $score;
        $matchedMoodKey = $key;
    }
}

// Query active books with category information
$allBooks = $dao->query("SELECT b.*, c.cname, c.cdesc FROM books b LEFT JOIN category c ON b.cid=c.cid WHERE b.status=1");

$scoredBooks = [];

if ($allBooks) {
    foreach ($allBooks as $book) {
        $relevanceScore = 0;
        $bookText = strtolower($book['bname'] . ' ' . $book['AuthorName'] . ' ' . $book['description'] . ' ' . $book['cname'] . ' ' . $book['cdesc']);

        // Check user query tokens directly against book text
        $tokens = preg_split('/\s+/', $inputQuery);
        foreach ($tokens as $token) {
            if (strlen($token) > 2 && strpos($bookText, $token) !== false) {
                $relevanceScore += 2;
            }
        }

        // Additional score if matched mood keywords appear in book description or category
        if ($matchedMoodKey) {
            foreach ($moodMap[$matchedMoodKey]['keywords'] as $kw) {
                if (strpos($bookText, $kw) !== false) {
                    $relevanceScore += 3;
                }
            }
        }

        // Check free vs paid preference if mentioned
        if (strpos($inputQuery, 'free') !== false && $book['is_paid'] == 'free') {
            $relevanceScore += 4;
        }
        if ((strpos($inputQuery, 'paid') !== false || strpos($inputQuery, 'premium') !== false) && $book['is_paid'] == 'paid') {
            $relevanceScore += 4;
        }

        $scoredBooks[] = [
            'book' => $book,
            'score' => $relevanceScore
        ];
    }
}

// Sort by relevance score descending
usort($scoredBooks, function($a, $b) {
    return $b['score'] <=> $a['score'];
});

// Select top 3-4 recommendations
$recommendedList = [];
foreach ($scoredBooks as $item) {
    if (count($recommendedList) < 4 && ($item['score'] > 0 || count($scoredBooks) <= 4)) {
        $recommendedList[] = $item['book'];
    }
}

// If no specific keyword matched, grab top rated / latest books as general suggestion
if (empty($recommendedList) && !empty($allBooks)) {
    $recommendedList = array_slice($allBooks, 0, 3);
}

// Prepare custom response greeting & narrative
$botIntro = "";
if ($matchedMoodKey && isset($moodMap[$matchedMoodKey])) {
    $botIntro = $moodMap[$matchedMoodKey]['intro'];
} elseif (strpos($inputQuery, 'hi') !== false || strpos($inputQuery, 'hello') !== false || strpos($inputQuery, 'hey') !== false) {
    $botIntro = "Hello there! 👋 I am **Libro AI**, your personal library companion. Tell me what mood you're in (e.g. peaceful, adventurous, romantic, suspenseful) or what kind of story you'd like to read today!";
} else {
    $botIntro = "Here are some delightful book recommendations from our digital collection based on your search! Explore the list below to find your next great read:";
}

// Format returned book payload for UI cards
$formattedBooks = [];
foreach ($recommendedList as $b) {
    $formattedBooks[] = [
        'bid' => $b['bid'],
        'title' => $b['bname'],
        'author' => $b['AuthorName'],
        'category' => $b['cname'] ?? 'General',
        'description' => substr($b['description'], 0, 110) . (strlen($b['description']) > 110 ? '...' : ''),
        'cover' => !empty($b['CoverImage']) ? '../uploads/' . $b['CoverImage'] : '../logo.png',
        'is_paid' => $b['is_paid'],
        'price' => $b['price']
    ];
}

echo json_encode([
    'status' => 'success',
    'reply' => $botIntro,
    'moodTitle' => $matchedMoodKey ? $moodMap[$matchedMoodKey]['title'] : null,
    'books' => $formattedBooks
]);
exit();
