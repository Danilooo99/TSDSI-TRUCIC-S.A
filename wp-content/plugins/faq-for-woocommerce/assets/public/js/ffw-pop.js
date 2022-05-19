/**
 * Woocommerce FAQ - Classic Template JS
 *
 * Copyright (c) 2019 wpfeel
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function ($) {
    'use strict';

    $.faq_pop = {
        init: function () {
            //default hide all answers
            $(document).on('click', '#ffw-main-wrapper .ffw-pop-container span', this.slideFAQs);
        },

        slideFAQs: function (e) {
            e.preventDefault();

            if ($(this).hasClass("ffw-active")) {
                $(this).removeClass("ffw-active").find(".ffw-classic-answer").slideUp();
            } else {
                $(".ffw-accordion-item > .ffw-button.ffw-active").parent('.ffw-accordion-item').find(".ffw-classic-answer").slideUp();
                $(".ffw-accordion-item > .ffw-button").removeClass("ffw-active");
                $(this).addClass("ffw-active").parent('.ffw-accordion-item').find(".ffw-classic-answer").slideDown();
            }

            return false;
        }


    }
    //call the class
    $.faq_pop.init();
});