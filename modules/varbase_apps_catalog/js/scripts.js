(function ($) {

    Drupal.behaviors.initSlider = {
      attach: function (context, settings) {
        if(context != document) return;
        $(context).ready(function(){
          $('.featured-carousel.slick-slider').slick({
            dots: true,
            prevArrow: '<button type="button" class="slick-prev"><</button>',
            nextArrow: '<button type="button" class="slick-next">></button>',
            speed: 800,
            autoplay: true,
          });
          $('.images-carousel.slick-slider').slick({
            dots: true,
            slidesToShow: 3,
            slidesToScroll: 3,
            prevArrow: '<button type="button" class="slick-prev"><</button>',
            nextArrow: '<button type="button" class="slick-next">></button>',
            speed: 800,
            autoplay: true,
          });
        });
  
      }
    };
  
  })(jQuery);