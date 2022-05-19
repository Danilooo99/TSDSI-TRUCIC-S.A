jQuery(document).ready(function ($) {
    //wow animatioin in site
    var wow = new WOW({});
    wow.init();

    //Header Search form show/hide
    $('html').on( 'click', function () {
        $('.site-header .form-holder').slideUp();
    });

    $('.site-header .form-section').on( 'click', function (event) {
        event.stopPropagation();
    });
    $("#btn-search").on( 'click', function () {
        $(".site-header .form-holder").slideToggle();
        return false;
    });

    //closebutton
    $(".btn-form-close").on( 'click', function () {
        $(".site-header .form-holder").slideToggle();
        return false;
    });

    //mobile menu
    var winWidth = $(window).width();
    if (winWidth < 1025) {
        // $('.mobile-navigation ul li.menu-item-has-children').append('<button class="arrow-down"><i class="fas fa-caret-down"></i></button>');


        // $('<button class="arrow-down"><i class="fas fa-caret-down"></i></button>').insertAfter($('.mobile-navigation ul .menu-item-has-children > a'));
        // $('.mobile-navigation ul li .arrow-down').click(function () {
        //     $(this).next().slideToggle();
        //     $(this).toggleClass('active');
        // });

        //customjs
        // $('#primary-toggle-button').click(function () {
        //     $('.mobile-navigation').slideToggle();
        //     $('#primary-toggle-button').css("display", "none");

        //     $('.close-main-nav-toggle').css("display", "block");
        //     $('.close-main-nav-toggle').css("display", "block");
        //     $('html').css('position', 'relative');

        // });

        // $(".close-main-nav-toggle").click(function () {
        //     $(".mobile-navigation").slideToggle();
        //     $('#primary-toggle-button').css("display", "block");
        // });
    }

     //ul accessibility
    $('<button class="arrow-down"><i class="fas fa-caret-down"></i></button>').insertAfter($('.mobile-navigation ul .menu-item-has-children > a'));
    $('.mobile-navigation ul li .arrow-down').on( 'click', function () {
        $(this).next().slideToggle();
        $(this).toggleClass('active');
    });

  
    if (winWidth > 1024) {
        $(".main-navigation ul li a").on( 'focus', function () {
            $(this).parents("li").addClass("hover");
        }).on( 'blur', function () {
            $(this).parents("li").removeClass("hover");
        });
    }
      //nav bar js
    $('#primary-toggle-button').on( 'click', function () {
        $('.mobile-navigation').slideToggle();
        $('#primary-toggle-button').css("display", "none");

        $('.close-main-nav-toggle').css("display", "block");
        $('.close-main-nav-toggle').css("display", "block");
        $('html').css('position', 'relative');

    });

    $(".close-main-nav-toggle").on( 'click', function () {
        $(".mobile-navigation").slideToggle();
        $('#primary-toggle-button').css("display", "block");
    });
});