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
        $('.passwordstrength').attr('style', 'background: rgba(255,0,0,0.7) !important; color: white');
        $('.passwordstrength').val('Minimum Length is '+minlength+', '+(minlength-testval.length)+" To Go");
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
    
    if (strength<=2)
    {
        $('.passwordstrength').attr('style', 'background: rgba(255,0,0,0.7) !important; color: white');
        //$('.passwordstrength').css('color', 'white');
        $('.passwordstrength').val('Weak');
        //background-color:rgba(255,0,0,0.5);
    }
    else if (strength==3)
    {
        $('.passwordstrength').attr('style', 'background: #FF8000 !important; color: white');
        //$('.passwordstrength').css('color', 'black');
        $('.passwordstrength').val('Moderate');
    }
    else if (strength>=4)
    {
        $('.passwordstrength').attr('style', 'background: rgba(34,139,34,0.7) !important; color: white');
        //$('.passwordstrength').css('color', 'white');
        $('.passwordstrength').val('Strong');
    }   
}