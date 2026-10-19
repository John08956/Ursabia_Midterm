<div class="modal-card">
    <div class="badge"><i class="fa-solid fa-shield-halved"></i> CRMC Attendance System</div>
    <a class="back" href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2><i class="fa-solid fa-user-plus"></i> Add Member</h2>
    <?php if ($message !== ''): ?>
        <div class="notice <?php echo $message_type === 'error' ? 'error' : ''; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST">
        <?php csrf_field(); ?>
        <div><label for="first_name">First Name</label><input id="first_name" name="first_name" required></div>
        <div><label for="last_name">Last Name</label><input id="last_name" name="last_name" required></div>
        <div>
            <label for="course">Course / Department</label>
            <select id="course" name="course" required>
                <option value="" disabled selected>Select course</option>
                <option>BSIT Department</option><option>BSCS Department</option><option>BSED Department</option>
                <option>BEED Department</option><option>BSCRIM Department</option><option>BSBA Department</option>
                <option>General Education</option>
            </select>
        </div>
        <div>
            <label for="year_level">Role / Year Level</label>
            <select id="year_level" name="year_level" required>
                <option value="" disabled selected>Select role</option><option>Faculty</option><option>Dean</option>
            </select>
        </div>
        <button class="save" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Member</button>
    </form>
</div>
