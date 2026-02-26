
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
<footer class="bg-gray-800 text-white mt-auto">
    <div class="container mx-auto px-4 py-6">
        <div class="text-center">
            <p class="text-sm" data-i18n="footer.copyright"></p>
            <p class="text-xs text-gray-400 mt-2">Sports Club Management System v2.0 Developed by <a href="https://github.com/Ministry-of-Sports-Southern-Province" target="_blank"><b><i>DPSP-DevOps</b></i></a></p>
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