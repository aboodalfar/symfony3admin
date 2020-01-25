function check_product_height()
{
    /*to avoid inner box style error*/
    var initial_height = 0;
    var max_height = 0;
    $(".aboutBlock .whyBaxia .whyBaxia-box.col-lg-6.col-md-6 .description").each(function () {
        var each_image_height = $(this).outerHeight();
        if (each_image_height > initial_height) {
            initial_height = each_image_height;
            max_height = each_image_height;
        }
    });
    $(".aboutBlock .whyBaxia .whyBaxia-box.col-lg-6.col-md-6 .description").css({'min-height': max_height + "px"});

    /*end to avoid inner box style error*/
}


$(document).ready(function(){


    $(".left-info-block").height($(".follow-theSteps").height());

    var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.maxHeight){
      panel.style.maxHeight = null;
    } else {
      panel.style.maxHeight = panel.scrollHeight + "px";
    } 
  });
}

  $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideDown("400");
            $(this).toggleClass('open');        
        },
        function() {
            $('.dropdown-menu', this).not('.in .dropdown-menu').stop(true,true).slideUp("400");
            $(this).toggleClass('open');       
        }
    );

	$( ".seach-toggle" ).click(function() {
		$( ".search-box" ).toggleClass( "bsquarebutton" );
	});

	$('.fundingMeythods').slick({
    		slidesToShow: 5,
    		slidesToScroll: 1,
        // autoplay: true,
        pauseOnHover: false,
        autoplaySpeed: 15000,
        responsive: [
        {
        	breakpoint: 1024,
        	settings: {
        		slidesToShow: 5,
        		slidesToScroll: 1,
        		infinite: true,
        		dots: true
        	}
        },
        {
        	breakpoint: 600,
        	settings: {
        		slidesToShow: 5,
        		slidesToScroll: 1
        	}
        },
        {
        	breakpoint: 480,
        	settings: {
        		slidesToShow: 4,
        		slidesToScroll: 1
        	}
        }
        ]
    });



    	$('.numAnd-text-slider').slick({
    		slidesToShow: 5,
    		slidesToScroll: 1,
        // autoplay: true,
        pauseOnHover: false,
        autoplaySpeed: 15000,
        responsive: [
        {
        	breakpoint: 1024,
        	settings: {
        		slidesToShow: 3,
        		slidesToScroll: 1,
        		infinite: true,
        		dots: true
        	}
        },
        {
        	breakpoint: 600,
        	settings: {
        		slidesToShow: 3,
        		slidesToScroll: 1
        	}
        },
        {
        	breakpoint: 480,
        	settings: {
        		slidesToShow: 3,
        		slidesToScroll: 1
        	}
        }
        ]
    });



	 var itaImgLink = "";
    	var engImgLink = "";
		var deuImgLink = "";
		var fraImgLink = "";

		var imgBtnSel = $('#imgBtnSel');
		var imgBtnIta = $('#imgBtnIta');
		var imgBtnEng = $('#imgBtnEng');
		var imgBtnDeu = $('#imgBtnDeu');
		var imgBtnFra = $('#imgBtnFra');

		var imgNavSel = $('#imgNavSel');
		var imgNavIta = $('#imgNavIta');
		var imgNavEng = $('#imgNavEng');
		var imgNavDeu = $('#imgNavDeu');
		var imgNavFra = $('#imgNavFra');

		var spanNavSel = $('#lanNavSel');
		var spanBtnSel = $('#lanBtnSel');

		imgBtnSel.attr("src",itaImgLink);
		imgBtnIta.attr("src",itaImgLink);
		imgBtnEng.attr("src",engImgLink);
		imgBtnDeu.attr("src",deuImgLink);
		imgBtnFra.attr("src",fraImgLink);

		imgNavSel.attr("src",itaImgLink);
		imgNavIta.attr("src",itaImgLink);
		imgNavEng.attr("src",engImgLink);
		imgNavDeu.attr("src",deuImgLink);
		imgNavFra.attr("src",fraImgLink);

		$( ".language" ).on( "click", function( event ) {
			var currentId = $(this).attr('id');

			if(currentId == "navIta") {
				imgNavSel.attr("src",itaImgLink);
				spanNavSel.text("ITA");
			} else if (currentId == "navEng") {
				imgNavSel.attr("src",engImgLink);
				spanNavSel.text("ENG");
			} else if (currentId == "navDeu") {
				imgNavSel.attr("src",deuImgLink);
				spanNavSel.text("DEU");
			} else if (currentId == "navFra") {
				imgNavSel.attr("src",fraImgLink);
				spanNavSel.text("FRA");
			}

			if(currentId == "btnIta") {
				imgBtnSel.attr("src",itaImgLink);
				spanBtnSel.text("ITA");
			} else if (currentId == "btnEng") {
				imgBtnSel.attr("src",engImgLink);
				spanBtnSel.text("ENG");
			} else if (currentId == "btnDeu") {
				imgBtnSel.attr("src",deuImgLink);
				spanBtnSel.text("DEU");
			} else if (currentId == "btnFra") {
				imgBtnSel.attr("src",fraImgLink);
				spanBtnSel.text("FRA");
			}
			
		});

     $('.baxiaMain-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: false,
        dots: true,
		fade: true,
        pauseOnHover: false,
        autoplaySpeed: 15000
    });

   // $('.selectpicker').selectpicker();


/******************************** Accordion  *******************************************/

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36251023-1']);
  _gaq.push(['_setDomainName', 'jqueryscript.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();


  /*************************************************/

    check_product_height();
    $(window).resize(function () {
        check_product_height();
    });

 

});
