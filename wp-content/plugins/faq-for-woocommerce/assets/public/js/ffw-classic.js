/**
 * Woocommerce FAQ - Classic Template JS
 *
 * Copyright (c) 2019 wpfeel
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function ($) {
    'use strict';

    $.faq_classic = {
        init: function () {
            //default hide all answers
            $('.ffw-accordion-item > .ffw-button:not(.ffw-active)').parent('.ffw-accordion-item').find(".ffw-classic-answer").hide();

            //accordian slide js
            $(document).on('click', '.ffw-accordion-item > .ffw-button', this.slideFAQs);
        },

        slideFAQs: function (e) {
            e.preventDefault();

            if ($(this).hasClass("ffw-active")) {
                $(this).removeClass("ffw-active").parent('.ffw-accordion-item').find(".ffw-classic-answer").slideUp();
            } else {
                $(".ffw-accordion-item > .ffw-button.ffw-active").parent('.ffw-accordion-item').find(".ffw-classic-answer").slideUp();
                $(".ffw-accordion-item > .ffw-button").removeClass("ffw-active");
                $(this).addClass("ffw-active").parent('.ffw-accordion-item').find(".ffw-classic-answer").slideDown();
            }

            return false;
        }


    };

    //call the class
    $.faq_classic.init();
});