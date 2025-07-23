<?php
include(__DIR__ . '/../user/includes/dbc.php');

$category_code = $_GET['category_code'] ?? '';

if ($category_code) {
    $stmt = $connect->prepare("SELECT item_code, name FROM items WHERE category_code = ?");
    $stmt->bind_param("s", $category_code);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
} else {
    echo json_encode([]);
}
