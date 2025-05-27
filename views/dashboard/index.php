<?php require_once 'views/layouts/header.php'; ?>

<div class="container">
    <div class="controls">
        <div>
            <h2>Network Status Dashboard</h2>
            <p style="color: #ccc;">Welcome back, <?= $_SESSION['username'] ?></p>
        </div>
        <div>
            <button class="btn" onclick="runMonitoring()">🔄 Check All Systems</button>
            <button class="btn btn-secondary" onclick="loadDashboard()">↻ Refresh</button>
        </div>
    </div>

    <div id="loading" class="loading">
        <div class="spinner"></div>
        <p>Loading system status...</p>
    </div>

    <div id="content"></div>
</div>

<script src="assets/js/dashboard.js"></script>

<?php require_once 'views/layouts/footer.php'; ?>