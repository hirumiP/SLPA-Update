<?php
session_start();
include(__DIR__ . '/../user/includes/dbc.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $unit_price = $_POST['unit_price'];
    $quantity = $_POST['quantity'];
    $remark = isset($_POST['remark']) ? $_POST['remark'] : '';

    if (isset($_POST['approve'])) {
        // Approve the request
        $sql = "UPDATE item_requests SET 
                status = 'Approved', 
                unit_price = ?, 
                quantity = ?, 
                remark = ? 
                WHERE request_id = ?";
        
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ddsi", $unit_price, $quantity, $remark, $request_id);
        
        if ($stmt->execute()) {
            header("Location: item_req.php?msg=approved");
        } else {
            header("Location: item_req.php?msg=error");
        }
        
    } elseif (isset($_POST['reject'])) {
        // Reject the request
        $sql = "UPDATE item_requests SET 
                status = 'Rejected', 
                remark = ? 
                WHERE request_id = ?";
        
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("si", $remark, $request_id);
        
        if ($stmt->execute()) {
            header("Location: item_req.php?msg=rejected");
        } else {
            header("Location: item_req.php?msg=error");
        }
    }
}
?>
