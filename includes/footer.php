    <!-- Floating Back to Top Button (Matches Image 2) -->
    <a href="#" class="back-to-top-btn" title="Back to Top" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;">
        &#9650;
    </a>

    <!-- Footer Component -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Col 1 -->
                <div class="footer-col">
                    <img src="./images/logo2.png" alt="Jordan Coat of Arms Emblem" class="footer-logo">
                    <p>Amman, Jordan</p>
                    <p>All Rights Reserved &copy; 2024</p>
                </div>

                <!-- Col 2 -->
                <div class="footer-col">
                    <h4>Contact Us</h4>
                    <p>National Contact Center: 065008080</p>
                    <p>Support: evisa.gov.jo</p>
                </div>

                <!-- Col 3 -->
                <div class="footer-col">
                    <h4>Download Server</h4>
                    <div class="app-badges">
                        <img src="./images/app-store.png" alt="App Store">
                        <img src="./images/google-pay.png" alt="Google Play">
                        <img src="./images/app-glary.png" alt="App Gallery">
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Use</a>
                </div>
                <div class="social-links">
                    <span style="margin-right: 6px;">Follow us:</span>
                    <a href="#">f</a>
                    <a href="#">X</a>
                    <a href="#">YT</a>
                    <a href="#">in</a>
                </div>
            </div>
        </div>
    </footer>

    <?php if (!empty($consoleError)): ?>
    <script>
        console.error("Jordan Visa Verification DB Error:", <?php echo json_encode($consoleError); ?>);
    </script>
    <?php endif; ?>

</body>
</html>
