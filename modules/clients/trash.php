<?php
include_once __DIR__ . '/../../config/path.php';
include_once __DIR__ . '/../../config/db.php';

include(BASE_PATH.'/includes/header.php');
include(BASE_PATH.'/includes/sidebar.php');

$res = mysqli_query($conn, "SELECT * FROM clients WHERE is_deleted = 1 ORDER BY deleted_at DESC");
?>

<div class="pcoded-content">
    <div class="header-box d-flex justify-content-between">
        <h2>Trash - Deleted Clients</h2>
        <a href="index.php" class="btn btn-primary">Back</a>
    </div>

    <div class="container mt-3">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Business</th>
                    <th>Email</th>
                    <th>Deleted At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php $i = 1; while($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['company']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= date("d-M-Y h:i A", strtotime($row['deleted_at'])) ?></td>

                    <td>
                        <a href="restore.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm"
                           onclick="return confirm('Restore this client?')">Restore</a>

                        <a href="delete_permanent.php?id=<?= $row['id'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete permanently? This cannot be undone!')">
                           Delete Forever
                        </a>
                    </td>

                </tr>
                <?php endwhile; ?>
            </tbody>

        </table>
    </div>
</div>

<?php include(BASE_PATH.'/includes/footer.php'); ?>
