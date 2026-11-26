
<?php
/* Administrator-only page for creating system accounts and displaying
 * the existing user list. New passwords are stored as password hashes.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin');

$error = '';

// Validate the submitted account details before inserting a new user.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    try {
        if (strlen($username) < 3) {
            throw new RuntimeException(
                'Username must contain at least 3 characters.'
            );
        }

        if (strlen($password) < 8) {
            throw new RuntimeException(
                'Password must contain at least 8 characters.'
            );
        }

        if (!in_array($role, ['admin', 'security'], true)) {
            throw new RuntimeException('Select a valid user role.');
        }

        $stmt = db()->prepare(
            'INSERT INTO users (username, password_hash, role)
             VALUES (?, ?, ?)'
        );

        $stmt->execute([
            $username,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        ]);

        audit_log(
            'user_created',
            $username . ' created with role ' . $role . '.'
        );

        flash('success', 'User account created successfully.');

        redirect('adduser.php');

    } catch (Throwable $e) {
        $error = $e instanceof PDOException
            ? 'That username is already in use.'
            : $e->getMessage();
    }
}

// Load the account list shown beneath the creation form.
$users = db()->query(
    'SELECT username, role, is_active, created_at
     FROM users
     ORDER BY username'
)->fetchAll();

$pageTitle = 'Add A New User';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Add A New User</h1>

<section class="card form-card">

    <?php if ($error): ?>
        <p class="notice error"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <label for="username">Username</label>
        <input
            id="username"
            name="username"
            required
            minlength="3"
        >

        <label for="password">Password (at least 8 characters)</label>
        <input
            id="password"
            name="password"
            type="password"
            required
            minlength="8"
        >

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="">Select role</option>
            <option value="admin">Administrator</option>
            <option value="security">Security Guard</option>
        </select>

        <button class="btn primary" type="submit">
            Create User
        </button>
    </form>

</section>

<section class="card">

    <h2>System Users</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= h($u['username']) ?></td>

                        <td><?= h(ucfirst($u['role'])) ?></td>

                        <td>
                            <?= $u['is_active'] ? 'Active' : 'Disabled' ?>
                        </td>

                        <td>
                            <?= date('d M Y', strtotime($u['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
