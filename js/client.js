/**
 * Shows and enables a countdown timer for the actual download
 */
(function(){
    "use strict";
    
    var spnTimeOut = document.querySelector('#spnTimeOut');
    var currentX = fwiepdl.download_timeout;

    if (spnTimeOut == null) {
        return false;
    }
    var x = setInterval(function() {
        currentX--;
        spnTimeOut.innerText = currentX;

        if (currentX <= 0) {
            clearInterval(x);
            window.location.href += '/1';
        }

    }, 1000);

}());