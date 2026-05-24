<?php

declare(strict_types=1);

$dbPath = dirname(__DIR__) . '/var/data.db';
if (!is_file($dbPath)) {
    fwrite(STDERR, "Database not found: {$dbPath}\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->query("PRAGMA table_info({$table})");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (($row['name'] ?? '') === $column) {
            return true;
        }
    }

    return false;
}

function addColumn(PDO $pdo, string $table, string $sql): void
{
    $pdo->exec($sql);
    echo "Added column on {$table}\n";
}

// recipes
$recipeColumns = [
    "status VARCHAR(20) NOT NULL DEFAULT 'published'",
    'created_at DATETIME DEFAULT NULL',
    'reviewed_at DATETIME DEFAULT NULL',
    'rejection_reason CLOB DEFAULT NULL',
    'deleted_at DATETIME DEFAULT NULL',
    'submitted_by_id INTEGER DEFAULT NULL',
    'reviewed_by_id INTEGER DEFAULT NULL',
];
foreach ($recipeColumns as $def) {
    $name = explode(' ', $def)[0];
    if (!columnExists($pdo, 'recipes', $name)) {
        addColumn($pdo, 'recipes', "ALTER TABLE recipes ADD COLUMN {$def}");
    }
}
if (columnExists($pdo, 'recipes', 'created_at')) {
    $pdo->exec("UPDATE recipes SET created_at = datetime('now') WHERE created_at IS NULL");
}

// community_tips
if ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='community_tips'")->fetch()) {
    $tipColumns = [
        "status VARCHAR(20) NOT NULL DEFAULT 'published'",
        'reviewed_at DATETIME DEFAULT NULL',
        'rejection_reason CLOB DEFAULT NULL',
        'submitted_by_id INTEGER DEFAULT NULL',
        'reviewed_by_id INTEGER DEFAULT NULL',
    ];
    foreach ($tipColumns as $def) {
        $name = explode(' ', $def)[0];
        if (!columnExists($pdo, 'community_tips', $name)) {
            addColumn($pdo, 'community_tips', "ALTER TABLE community_tips ADD COLUMN {$def}");
        }
    }
    $pdo->exec("UPDATE community_tips SET status = 'published' WHERE status IS NULL OR status = ''");
}

// New tables (minimal SQLite DDL)
$tables = [
    'users' => <<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    email VARCHAR(180) NOT NULL,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    roles CLOB NOT NULL,
    created_at DATETIME NOT NULL
)
SQL,
    'notifications' => <<<'SQL'
CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    recipient_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message CLOB NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read BOOLEAN NOT NULL,
    created_at DATETIME NOT NULL,
    related_recipe_id INTEGER DEFAULT NULL,
    related_tip_id INTEGER DEFAULT NULL
)
SQL,
    'user_favorites' => <<<'SQL'
CREATE TABLE IF NOT EXISTS user_favorites (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    user_id INTEGER NOT NULL,
    recipe_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL
)
SQL,
    'recipe_ratings' => <<<'SQL'
CREATE TABLE IF NOT EXISTS recipe_ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    user_id INTEGER NOT NULL,
    recipe_id INTEGER NOT NULL,
    score INTEGER NOT NULL,
    created_at DATETIME NOT NULL
)
SQL,
    'recipe_comments' => <<<'SQL'
CREATE TABLE IF NOT EXISTS recipe_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    author_id INTEGER NOT NULL,
    recipe_id INTEGER NOT NULL,
    content CLOB NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL,
    reviewed_by_id INTEGER DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL
)
SQL,
    'admin_activity_logs' => <<<'SQL'
CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    admin_id INTEGER NOT NULL,
    action VARCHAR(80) NOT NULL,
    details CLOB NOT NULL,
    created_at DATETIME NOT NULL
)
SQL,
];

foreach ($tables as $name => $sql) {
    $pdo->exec($sql);
    echo "Ensured table {$name}\n";
}

$pdo->exec("UPDATE recipes SET status = 'published' WHERE status IS NULL OR status = ''");

echo "SQLite schema patch complete.\n";
