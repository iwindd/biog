<?php
$copyrightText = 'Copyright ©2020 All Rgiht Reserved. สำนักงานพัฒนาเศรษฐกิจจากฐานชีวภาพ (องค์การมหาชน)';
?>


<footer class="footer-container mt-3 ">
    <div class="footer-link py-3 d-flex justify-content-center">
        <div class="about mx-4 mobile-footer-menu">
            <a href="/about" class="text-secondary">about</a>
        </div>
        <span class="text-secondary vertical-line mobile-line">|</span>

        <div class="news mx-4 mobile-footer-menu">
            <a href="/news" class="text-secondary">news</a>
        </div>
        <span class="text-secondary vertical-line mobile-line">|</span>

        <div class="privacy-policy mx-4 mobile-footer-menu">
            <a href="/terms-conditions" class="text-secondary">terms & conditions</a>
        </div>
        <span class="text-secondary vertical-line mobile-line">|</span>

        <div class="protection-policy mx-4 mobile-footer-menu">
            <a href="/data-protection-policy" class="text-secondary">data protection policy</a>
        </div>

        <hr class="mobile-hr"> 
    </div> 
    <div class="copyright d-flex py-4 justify-content-center">
        <p class="h5 text"><?= $copyrightText; ?></p>
    </div>
</footer>