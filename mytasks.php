<!-- Desktop View for Posted Tasks -->
<div class="table-responsive d-none d-lg-block">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th></th>
                <th>Task</th>
                <th>Category</th>
                <th>Status</th>
                <th>Budget</th>
                <th>Created</th>
                <th>Magic Word</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posted_tasks as $task): ?>
                <tr>
                    <td>
                        <?php if (!empty($task_applications[$task['id']])): ?>
                            <!-- ... existing code ... -->
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- ... existing code ... -->
                    </td>
                    <td><?= ucfirst($task['category']) ?></td>
                    <td>
                        <span class="badge badge-<?= getStatusBadge($task['status']) ?>">
                            <?= ucfirst($task['status']) ?>
                        </span>
                    </td>
                    <td><?= $task['pay'] ? '₱' . number_format($task['pay'], 2) : 'None' ?></td>
                    <td><?= date('M d, Y', strtotime($task['created_at'])) ?></td>
                    <td>
                        <?php if (!empty($task['magic_word'])): ?>
                            <span class="badge bg-success"><?= htmlspecialchars($task['magic_word']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- ... existing code ... -->
                    </td>
                </tr>
                <!-- ... existing code ... -->
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Desktop View for Applied Tasks -->
<div class="table-responsive d-none d-lg-block">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Task</th>
                <th>Category</th>
                <th>Status</th>
                <th>Budget</th>
                <th>Applied</th>
                <th>Magic Word</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applied_tasks as $task): ?>
                <tr>
                    <!-- ... existing code ... -->
                    <td>
                        <?php if ($task['application_status'] == 'accepted' && !empty($task['magic_word'])): ?>
                            <span class="badge bg-success"><?= htmlspecialchars($task['magic_word']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- ... existing code ... -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

// ... existing code ... 