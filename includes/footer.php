<?php

/**
 * Reusable Footer Component
 * 
 * @param array $scripts - Additional JavaScript files to include (optional)
 * @param string $basePath - Base path for assets (defaults to '../' for public folder pages)
 */

// Default values
$scripts = $scripts ?? [];
$basePath = $basePath ?? '../';
?>
<!-- Footer -->
<footer class="bg-gray-800 text-white mt-12">
    <div class="container mx-auto px-4 py-6">
        <div class="text-center">
            <p class="text-sm" data-i18n="footer.copyright">&copy; <?php echo date('Y'); ?> දකුණු පළාත් ක්රීඩා අමාත්යාංශය. සියලුම හිමිකම් ඇවිරිණි.</p>
            <p class="text-xs text-gray-400 mt-2" data-i18n="footer.version">Sports Club Management System v2.0 Developed by Gavindu Gayashan</p>
        </div>
    </div>
</footer>

<!-- i18n JavaScript (Always included) -->
<script src="<?php echo $basePath; ?>assets/js/i18n.js"></script>

<!-- Additional Libraries (if any) -->
<?php if (isset($additionalLinks) && !empty($additionalLinks)): ?>
    <?php foreach ($additionalLinks as $link): ?>
        <?php echo $link . "\n    "; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php foreach ($scripts as $script): ?>
    <script src="<?php echo htmlspecialchars($script); ?>"></script>
<?php endforeach; ?>
</body>

</html>