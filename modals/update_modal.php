<div class="modal-card">
    <div class="badge"><i class="fa-solid fa-shield-halved"></i> CRMC Attendance System</div>
    <a class="back" href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2><i class="fa-solid fa-pen-to-square"></i> Update Member</h2>
    <?php if ($message !== ''): ?>
        <div class="notice <?php echo $message_type === 'error' ? 'error' : ''; ?>"><?php echo htmlspecialchars(strip_tags($message)); ?></div>
    <?php endif; ?>
    <form action="update.php?id=<?php echo htmlspecialchars((string)$member_id); ?>" method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="member_id" value="<?php echo htmlspecialchars((string)($member['member_id'] ?? '')); ?>">
        <div><label for="first_name">First Name</label><input id="first_name" type="text" name="first_name" value="<?php echo htmlspecialchars($member['first_name'] ?? ''); ?>" required></div>
        <div><label for="last_name">Last Name</label><input id="last_name" type="text" name="last_name" value="<?php echo htmlspecialchars($member['last_name'] ?? ''); ?>" required></div>
        <div><label for="course">Course</label><input id="course" type="text" name="course" value="<?php echo htmlspecialchars($member['course'] ?? ''); ?>" required></div>
        <div><label for="year_level">Year Level</label><input id="year_level" type="text" name="year_level" value="<?php echo htmlspecialchars($member['year_level'] ?? ''); ?>" required></div>
        <button class="save" type="submit"><i class="fa-solid fa-floppy-disk"></i> Update Member</button>
    </form>
</div>
