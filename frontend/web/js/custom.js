$(document).ready(function () {
    let fontSize = localStorage.getItem('fontSize')
    let fontSizeTxt = fontSize + 'px'
    $('html').css('fontSize', fontSizeTxt)
})

window.onscroll = () => {
    const nav = document.querySelector('#navbar')
    if (this.scrollY <= 10) nav.className = 'navbar fixed-top navbar-expand-lg navbar-light bg-theme-style menu-header p-0'
    else nav.className = 'navbar fixed-top navbar-expand-lg navbar-light bg-theme-style menu-header p-0 white-soft'
}

function showMapContact() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 18.7433468, lng: 98.923161 },
        zoom: 8,
    })
}

function likeSubmit(id, site) {
    if ($('.like').hasClass('active')) {
        $.post('/site/submit-like', { id: id, site: site, active: 'dislike' }, function (data) {
            $('.like').removeClass('active')
        })
    } else {
        $.post('/site/submit-like', { id: id, site: site, active: 'like' }, function (data) {
            $('.like').addClass('active')
        })
    }
}

function ConfirmDelete() {
    var x = confirm('คุณต้องการลบข้อความนี้ใช่หรือไม่')
    if (x) return true
    else return false
}

// $(".comment-form").submit(function(event) {
//      event.preventDefault(); // stopping submitting
//      var data = $(this).serializeArray();
//      var url = $(this).attr('action');
//      $.ajax({
//          url: url,
//          type: 'post',
//          dataType: 'json',
//          data: data,
//          success: function(response) {
//            location.reload()
//           //loadCommentList(response.data.model.blog_id);
//          },
//          error: function() {
//             console.log('error')
//         }
//      });
//  });

$('.btn-post').click(function () {
    let message = $('#comment-input').val()
    let blog_id = $('#blog-id').val()
    if (message != '') {
        $.ajax({
            type: 'POST',
            url: '/comment/create-blog-comment',
            //cache: false,
            data: { message: message, blog_id: blog_id },
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            error: function (err) {},
            success: function (res) {
                $('.empty-data').remove()
                $('#comment-input').val('')
                let div =
                    '<div class="comment-list blog-comment-list" data-id="' +
                    res.data.id +
                    '">' +
                    "<div class='row'>" +
                    '<span class="option-comment">' +
                    '<i class="fas fa-trash-alt"></i>' +
                    '</span>' +
                    "<div class='profile-pic'>" +
                    "<img src='" +
                    res.data.picture +
                    "' alt='' class='img-rounded'>" +
                    '</div>' +
                    "<div class='profile-timestamp'>" +
                    "<p class='title-user'>" +
                    res.data.fullname +
                    '</p>' +
                    "<p class='post'>โพสต์ " +
                    res.data.time +
                    'น. วันที่ ' +
                    res.data.date +
                    '</p>' +
                    '</div>' +
                    '</div>' +
                    "<p class='message'>" +
                    message +
                    '</p>' +
                    '</div>';
                    $('.block-comment').prepend(div)
                    $('.comment-list.blog-comment-list .option-comment').click(function (e) {
                        let element = this.parentElement.parentElement
                        let id = $(element).data('id')
                       console.log(element)
                        Swal.fire({
                            type: "warning",
                            title: 'คุณต้องการลบความคิดเห็นนี้หรือไม่',
                            showCancelButton: true,
                            confirmButtonColor: "#d33",
                            cancelButtonColor: "#3085d6",
                            confirmButtonText: 'ลบ',
                            cancelButtonText: 'ยกเลิก',
                          }).then((result) => {
                            if (result.value) {
                              $.ajax({
                                method: "POST",
                                url: "/comment/delete-blog-comment",
                                cache: false,
                                data: { id: id},
                                dataType: 'json',
                                contentType: 'application/x-www-form-urlencoded',
                                success: function (res) {
                                    console.log(res)
                                    $(element).remove()
                                },
                              });
                              $(element).remove()
                            }
                           
                        });
                    })
                
            },
        })
    }
})

$('.btn-content-post').click(function () {
    let message = $('#comment-input').val()
    let content_id = $('#content-id').val()
    if (message != '') {
        $.ajax({
            type: 'POST',
            url: '/comment/create-content-comment',
            //cache: false,
            data: { message: message, content_id: content_id },
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            error: function (err) {},
            success: function (res) {
                $('.empty-data').remove()
                $('#comment-input').val('')
                let div =
                    '<div class="comment-list" data-id="' +
                    res.data.id +
                    '">' +
                    '<div class="row">' +
                    '<span class="option-comment">' +
                    '<i class="fas fa-trash-alt"></i>' +
                    '</span>' +
                    "<div class='profile-pic'>" +
                    "<img src='" +
                    res.data.picture +
                    "' alt='' class='img-rounded'>" +
                    '</div>' +
                    "<div class='profile-timestamp'>" +
                    "<p class='title-user'>" +
                    res.data.fullname +
                    '</p>' +
                    "<p class='post'>โพสต์ " +
                    res.data.time +
                    'น. วันที่ ' +
                    res.data.date +
                    '</p>' +
                    '</div>' +
                    '</div>' +
                    "<p class='message'>" +
                    message +
                    '</p>' +
                    '</div>'
                $('.block-comment').prepend(div)
                $('.comment-list .option-comment').click(function (e) {
                    let element = this.parentElement.parentElement
                    let id = $(element).data('id')
                    Swal.fire({
                        type: "warning",
                        title: 'ยืนยันการลบ',
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: 'ลบ',
                        cancelButtonText: 'ยกเลิก',
                      }).then((result) => {
                        if (result.value) {
                          $.ajax({
                            method: "POST",
                            url: "/comment/delete-content-comment",
                            cache: false,
                            data: { id: id},
                            dataType: 'json',
                            contentType: 'application/x-www-form-urlencoded',
                            error: function (err) {},
                            success: function (res) {
                                $(element).remove()
                            },
                          });
                          $(element).remove()
                        }
                        
                    });
                })
            },
        })
    }
})

window.onclick = function (event) {
    if (!event.target.matches('.share-label')) {
        var dropdowns = document.getElementsByClassName('dropdown-content')
        var i
        for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i]
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show')
            }
        }
    }
}

$('.share-label').click(function (e) {
    e.preventDefault()
    $('.share-dropdown-item').removeClass('show')
    let current = e.currentTarget.parentElement
    $(current).find('.share-dropdown-item').toggleClass('show')
})

// window.onclick = function (event) {
//     if (!event.target.matches('.fa-ellipsis-h')) {
//         var dropdowns = document.getElementsByClassName('dropdown-content')
//         var i
//         for (i = 0; i < dropdowns.length; i++) {
//             var openDropdown = dropdowns[i]
//             if (openDropdown.classList.contains('show')) {
//                 openDropdown.classList.remove('show')
//             }
//         }
//     }
// }

// $('.fa-ellipsis-h').click(function (e) {
//     e.preventDefault()
//     $('.share-dropdown-item').removeClass('show')
//     let current = e.currentTarget.parentElement
//     $(current).find('.share-dropdown-item').toggleClass('show')
// })

$('.yellow .nav-link').click(function (e) {
    e.preventDefault()
    let thisFontSize = document.getElementsByTagName('html')[0].style.fontSize.substring(0, 2)
    //let thisFontSize = $("html")[0].style.fontSize
    let decreaseFontSize = thisFontSize

    if (decreaseFontSize > 14) {
        decreaseFontSize = parseInt(decreaseFontSize) - 2
    }

    if (typeof Storage !== 'undefined') {
        localStorage.setItem('fontSize', decreaseFontSize)
    } else {
        localStorage.setItem('fontSize', 16)
    }

    let fontSizeTxt = decreaseFontSize + 'px'
    $('html').css('fontSize', fontSizeTxt)
    //document.getElementsByTagName("html").style.fontSize = fontSizeTxt;
})

$('.black .nav-link').click(function (e) {
    e.preventDefault()
    if (typeof Storage !== 'undefined') {
        localStorage.setItem('fontSize', 16)
    }
    $('html').css('fontSize', '16px')
})

$('.white .nav-link').click(function (e) {
    e.preventDefault()
    let thisFontSize = document.getElementsByTagName('html')[0].style.fontSize.substring(0, 2)

    let increaseFontSize = thisFontSize

    if (increaseFontSize < 18) {
        increaseFontSize = parseInt(increaseFontSize) + 2
    }

    if (typeof Storage !== 'undefined') {
        localStorage.setItem('fontSize', increaseFontSize)
    } else {
        localStorage.setItem('fontSize', 16)
    }

    let fontSizeTxt = increaseFontSize + 'px'
    $('html').css('fontSize', fontSizeTxt)
})

$('.fb-share').click(function (e) {
    e.preventDefault()
    window.open(
        $(this).attr('href'),
        'fbShareWindow',
        'height=450, width=550' + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0'
    )
    return false
})


$('.comment-list .option-comment').click(function (e) {
    let element = this.parentElement.parentElement
    let id = $(element).data('id')
    //$(element).remove()
    //$.post('/comment/delete-content-comment', { id: id }, function (data) {})

    Swal.fire({
        type: "warning",
        title: 'คุณต้องการลบความคิดเห็นนี้หรือไม่',
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
      }).then((result) => {
        if (result.value) {
          $.ajax({
            method: "POST",
            url: "/comment/delete-content-comment",
            cache: false,
            data: { id: id},
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            success: function (res) {
                $(element).remove()
            },
          });
          $(element).remove()
        }
        
    });
})

$('.comment-list.blog-comment-list .option-comment').click(function (e) {
    let element = this.parentElement.parentElement
    let id = $(element).data('id')
   console.log(element)
    Swal.fire({
        type: "warning",
        title: 'คุณต้องการลบความคิดเห็นนี้หรือไม่',
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
      }).then((result) => {
        if (result.value) {
          $.ajax({
            method: "POST",
            url: "/comment/delete-blog-comment",
            cache: false,
            data: { id: id},
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            success: function (res) {
                $(element).remove()
            },
          });
          $(element).remove()
        }
        
    });
})

$(".delete-wallbaord").click(function (e) {
    let element = this.parentElement.parentElement.parentElement
    let id = $(element).data('id')
    Swal.fire({
        type: "warning",
        title: 'คุณต้องการลบ Wallboard นี้หรือไม่',
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
      }).then((result) => {
        if (result.value) {
          $.ajax({
            method: "POST",
            url: "/wallboard/delete",
            cache: false,
            data: { id: id},
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            success: function (res) {
                $(element).remove()
            },
          });
          $(element).remove()
        }
        
    });
});

function deleteBlog(id) {
    Swal.fire({
        type: "warning",
        title: 'ต้องการลบเนื้อหานี้ใช่หรือไม่',
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก',
      }).then((result) => {
        if (result.value) {
          $.ajax({
            method: "GET",
            url: "/blog/delete/" + id,
            cache: false,
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            success: function (res) {
                //deleted
            },
          });
        }
        
    });
}

$('#show-menu-mobile').on('click', function(){
    console.log($( window ).width());
    let widthLayout = $( window ).width();
    if(widthLayout == 768){
        widthLayout = widthLayout - 100;
    }else if(widthLayout >= 425 && widthLayout <= 426){
        widthLayout = widthLayout - 100;
    }else if(widthLayout >= 414 && widthLayout <= 415){
        widthLayout = widthLayout - 100;
    }else{
        widthLayout = widthLayout - 100;
    }
    if(!$("#show-menu-mobile").hasClass("tg")){
        $('#show-menu-mobile').addClass('tg');
        $('.mobile-layout').css({
            'width' : widthLayout+'px',
            'overflow-x' : 'scroll'
        });
    }else{
        $('#show-menu-mobile').removeClass('tg');
        $('.mobile-layout').css({
            'width': '91px',
            'overflow-x' : 'hidden'
        });
    }
    
})


$('.navbar-toggler').on('click', function(){
    if(!$("#navbar-collapse").hasClass("show")){
        $('.menu-mobile').animate({ "top": "263px" }, 250 );
    }else{
        $('.menu-mobile').animate({ "top": "107px" }, 250 );
    }
});