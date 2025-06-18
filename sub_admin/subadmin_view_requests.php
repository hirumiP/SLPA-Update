<?php
include(__DIR__ . '/../user/includes/dbc.php');

// Fetch all item requests
$sql = "SELECT ir.id, ir.division, i.name AS item_name, b.budget AS budget_name, ir.year, ir.quantity, ir.reason, ir.description, ir.status
        FROM item_requests ir
        LEFT JOIN items i ON ir.item_code = i.item_code
        LEFT JOIN budget b ON ir.budget_id = b.id";
$result = $connect->query($sql);
?>

<div class="container">
    <h2 class="text-center mb-4">Pending Item Requests</h2>
    <table class="table table-bordered text-center">
        <thead>
            <tr>
                <th>Division</th>
                <th>Item Name</th>
                <th>Budget Name</th>
                <th>Year</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
                echo "
                <tr>
                    <td>{$row['division']}</td>
                    <td>{$row['item_name']}</td>
                    <td>{$row['budget_name']}</td>
                    <td>{$row['year']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['reason']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <form method='POST' action='update_status.php'>
                            <input type='hidden' name='request_id' value='{$row['id']}'>
                            <select name='status' class='form-select'>
                                <option value='Approved'>Approve</option>
                                <option value='Rejected'>Reject</option>
                            </select>
                            <button type='submit' class='btn btn-primary btn-sm mt-2'>Update</button>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
