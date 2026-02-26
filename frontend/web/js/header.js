$(document).ready(function () {
    $(document).on("click", ".navbar-toggler", function () {
        if (!$(".navbar-toggler").hasClass("collapsed")) {
            setTimeout(function () {
                if ($(".navbar-collapse").hasClass("show")) {
                    let navbarHeight = $(".navbar").height();
                    console.log(navbarHeight);

                    $(".background-navbar").css({
                        "max-height": navbarHeight,
                    });
                }
            }, 20);
        } else {
            $(".background-navbar").css({
                "max-height": "107px",
            });
        }
    });
});
