$(document).ready(function(e) {
    
    passwordField = $('input[type="password"]');
    
    var testval = passwordField.val();
    testpasswordstrength(testval, passwordField.data('minlength'));  
    
    
    passwordField.keyup( function() {
        var testval = $(this).val();
        testpasswordstrength(testval, $(this).data('minlength') );
    });
    
});

function testpasswordstrength(testval, minlength)
{
    var strength = 0;
    //var testval = $(this).val();

    if (minlength > testval.length)
    {
        $('.passwordstrength').css('color', 'rgba(255,0,0,0.7)');
        $('.passwordstrength').html('Too Short');
        return;
    }
    
    var numberpattern = /[0-9]/g;
    if ( numberpattern.test(testval) ) strength++;
    
    var uletterpattern = /[A-Z]/g;
    if ( uletterpattern.test(testval) ) strength++;
    
    var lletterpattern = /[a-z]/g;
    if ( lletterpattern.test(testval) ) strength++;
    
    var specialcharpattern = /[\W+]/g;
    if ( specialcharpattern.test(testval) ) strength++;
    
    if (strength==0 || strength==1 || strength==2)
    {
        $('.passwordstrength').css('color', 'rgba(255,0,0,0.7)');
        //$('.passwordstrength').css('color', 'white');
        $('.passwordstrength').html('Weak');
        //background-color:rgba(255,0,0,0.5);
    }
    else if (strength==3)
    {
        $('.passwordstrength').css('color', '#FF8000');
        //$('.passwordstrength').css('color', 'black');
        $('.passwordstrength').html('Moderate');
    }
    else if (strength==4)
    {
        $('.passwordstrength').css('color', 'rgba(34,139,34,0.7)');
        //$('.passwordstrength').css('color', 'white');
        $('.passwordstrength').html('Strong');
    }   
}