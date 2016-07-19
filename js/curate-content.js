(function($){

	var curateContent ={

		init:function(){

			curateContent.controls();

		}, 
		controls: function(){

			var sourceInputs = $(".curate-video-source-inputs");

			sourceInputs.on("click", function(){

				// console.log("happening?", sourceInputs.find("input[type='radio']").prop('checked'));

				// sourceInputs.find("input[type='radio']").prop("checked", false);

			});

		}

	};
	curateContent.init();

})(jQuery);