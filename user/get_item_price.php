<?php
include(__DIR__ . '/../user/includes/dbc.php');

if (isset($_GET['item_code'])) {
    $item_code = $_GET['item_code'];

    $stmt = $connect->prepare("SELECT unit_price FROM items WHERE item_code = ?");
    $stmt->bind_param("s", $item_code);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();

    echo json_encode(['price' => $price]);
    $stmt->close();
}
?>
