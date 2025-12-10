$(document).ready(function () {
	$("input").on("focus", function () {
        // Apne input ke baad jo error message h usko hide/remove karo
        $(this).closest('.form-group').find('.text-danger').fadeOut(200, function(){
            $(this).remove(); // completely hata do DOM se
        });
    });
// Password show/hide toggle
	    $(".toggle-password").click(function () {
        let input = $($(this).data("target"));
        if (input.attr("type") === "password") {
            input.attr("type", "text");
            $(this).removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.attr("type", "password");
            $(this).removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });

	
});

"use strict";

