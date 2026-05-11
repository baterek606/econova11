<?php
$db = new PDO('sqlite:c:/xampp/htdocs/econova1/econova.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check existing columns in 'campaigns' table
$stmt = $db->query('PRAGMA table_info(campaigns)');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);

$existing_columns = [];
foreach ($cols as $c) {
    $existing_columns[] = $c['name'];
}

$required_columns = [
    'title' => 'TEXT',
    'description' => 'TEXT',
    'target_amount' => 'INTEGER',
    'raised_amount' => 'INTEGER',
    'days_left' => 'INTEGER',
    'engagement_count' => 'INTEGER',
    'status' => 'TEXT',
    'badge' => 'TEXT',
    'image_url' => 'TEXT'
];

foreach ($required_columns as $col => $type) {
    if (!in_array($col, $existing_columns)) {
        echo "Adding column $col to campaigns...\n";
        $db->exec("ALTER TABLE campaigns ADD COLUMN $col $type");
    }
}

// Insert campaigns
$campaigns = [
    [
        'title' => 'Green Cairo: 10,000 Trees for Clean Air',
        'description' => 'Help us plant 10,000 native trees across Cairo to combat air pollution and create green spaces for local communities.',
        'target_amount' => 25000,
        'raised_amount' => 5000,
        'days_left' => 45,
        'engagement_count' => 120,
        'status' => 'ACTIVE',
        'badge' => 'URBAN',
        'image_url' => 'local'
    ],
    [
        'title' => 'Save the Mangroves: Sinai Coast',
        'description' => 'Protect and restore mangrove forests along the Sinai Peninsula to preserve marine biodiversity and prevent coastal erosion.',
        'target_amount' => 40000,
        'raised_amount' => 12000,
        'days_left' => 60,
        'engagement_count' => 85,
        'status' => 'ACTIVE',
        'badge' => 'COASTAL',
        'image_url' => 'local'
    ],
    [
        'title' => 'Zero Waste Alexandria: Beach Plastic Cleanup',
        'description' => 'Remove 10,000 kg of plastic waste from Alexandria beaches and educate local communities about recycling and waste management.',
        'target_amount' => 15000,
        'raised_amount' => 8000,
        'days_left' => 30,
        'engagement_count' => 200,
        'status' => 'ACTIVE',
        'badge' => 'BEACH',
        'image_url' => 'local'
    ]
];

$stmt = $db->prepare("INSERT INTO campaigns (title, description, target_amount, raised_amount, days_left, engagement_count, status, badge, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($campaigns as $c) {
    $stmt->execute([
        $c['title'],
        $c['description'],
        $c['target_amount'],
        $c['raised_amount'],
        $c['days_left'],
        $c['engagement_count'],
        $c['status'],
        $c['badge'],
        $c['image_url']
    ]);
}

echo "Successfully added 3 campaigns!\n";
?>
