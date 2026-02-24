<div class="background-menu">
    <img src="<?= $background; ?>" alt="" class="img-fluid" style="object-fit: cover; min-height: 500px;">
</div>

<div class="main-menu container position-relative">

    <div class="container banner-content ">
        <div class="search-box">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <form action="/search" method="GET" id="search-form">
                        <div class="w-100">
                            <div class="input-group">
                                <input class="form-control input-lg search-input kanit" type="text" placeholder="ใส่คำค้นหาได้ที่นี่" name="keyword">
                                <div class="input-group-append">
                                    <button type="submit" class="btn-search input-group-text amber lighten-3 pr-3 pl-3"><i class="fas fa-search text-grey" aria-hidden="true"></i> <span>ค้นหา</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="main-menu-item">
                <div class="background-icon">
                    <img src="/images/icon/Home.svg">
                </div>
                <span>Home</span>
            </div>
            <div class="main-menu-item">
                <div class="background-icon">
                    <img src="/images/icon/News.svg">
                </div>
                <span>News</span>
            </div>
            <div class="main-menu-item">
                <div class="background-icon">
                    <img src="/images/icon/Knowledge.svg">
                </div>
                <span>Knowledge</span>
            </div>
            <div class="main-menu-item">
                <div class="background-icon">
                    <img src="/images/icon/Blog.svg">
                </div>
                <span>Blog</span>
            </div>
            <div class="main-menu-item">
                <div class="background-icon">
                    <img src="/images/icon/Contact.svg">
                </div>
                <span>Contact</span>
            </div>

        </div>
    </div>

</div>