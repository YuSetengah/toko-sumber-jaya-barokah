// BANNER CAROUSEL
$(document).ready(function () {
    $(".owl-carousel").owlCarousel({
        items: 1,
        loop: true,
        autoplay: true,
        autoplayTimeout: 4000,
        dots: true
    });
});

// COUNTDOWN FLASH SALE
(function () {
    var input = document.getElementById("time_count_flash_sale");
    if (!input) return;

    var target = new Date(input.value).getTime();
    var elDays = document.getElementById("days");
    var elHours = document.getElementById("hours");
    var elMinutes = document.getElementById("minutes");
    var elSeconds = document.getElementById("seconds");
    if (!elDays || isNaN(target)) return;

    function pad(n) { return n < 10 ? "0" + n : n; }

    function tick() {
        var distance = target - new Date().getTime();
        if (distance <= 0) {
            location.reload(); // flash sale selesai, muat ulang agar bagian ini hilang
            return;
        }
        elDays.textContent = pad(Math.floor(distance / 86400000));
        elHours.textContent = pad(Math.floor((distance % 86400000) / 3600000));
        elMinutes.textContent = pad(Math.floor((distance % 3600000) / 60000));
        elSeconds.textContent = pad(Math.floor((distance % 60000) / 1000));
    }

    tick();
    setInterval(tick, 1000);
})();
