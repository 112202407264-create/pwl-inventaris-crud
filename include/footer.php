<?php
$baseUrl = isset($baseUrl) ? $baseUrl : '';
?>
</main>

<footer class="app-footer">
    <div class="container">
        <div class="app-footer-inner">
            <div class="app-footer-brand">
                <span class="app-brand-icon">◆</span>
                Inventaris Sepatu
            </div>
            <div class="app-footer-links">
                <a href="<?= $baseUrl ?>pages/dashboard.php">Dashboard</a>
                <a href="<?= $baseUrl ?>index.php">Data Barang</a>
                <a href="<?= $baseUrl ?>tambah.php">Tambah Barang</a>
            </div>
            <div class="app-footer-copy">
                &copy; <?= date('Y') ?> Inventaris Sepatu. Semua hak dilindungi.
            </div>
        </div>
    </div>
</footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
