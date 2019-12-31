"use strict";

var ready = (callback) => {
    if (document.readyState != "loading") {
        callback();
    } else {
        document.addEventListener("DOMContentLoaded", callback);
    }
}
ready(() => {
    "use strict";

    // Set the progress bar value (0 > n > 100)
    function setProgress(n){
        n = parseInt(n);
        n = (n < 0 ? 0 : n);
        n = (n > 100 ? 100 : n);
                
        var pbt = document.getElementById('pbar');
        pbt.style.width = (n + '%');
        pbt.ariaValueNow = n;
        // Because Firefox doesn't understand .ariaValueNow, we have to be creative...
        // See https://developer.mozilla.org/en-US/docs/Web/API/Element/ariaValueNow#browser_compatibility
        pbt.setAttribute('aria-valuenow', n);
        pbt.textContent = (n + '%');
    }

    // Reset the form
    function resetForm(){
        document.querySelectorAll('form.ajax').forEach((f) => {
            f.reset();
        });
        document.querySelectorAll('input[name="inpType"]').forEach((i) => {
            i.disabled = false;
        });
        let irof = document.getElementById('inpReadOnlyFilename');
        irof.classList.remove('show')
        irof.value = '';
        document.getElementById('cardExternal').classList.remove('show');
        document.getElementById('cardLocal').classList.remove('show');
    }

    // Make fields required when type is selected
    // Show appropriate panel on radio selection
    document.querySelectorAll('input[name="inpType"]').forEach((i) => {
        i.addEventListener('click', (ev) => {
            let inpLongUrl = document.getElementById('inpLongUrl');
            let inpLocalFile = document.getElementById('inpLocalFile');

            inpLongUrl.required = false;
            inpLocalFile.required = false;

            switch (i.value) {
                case 'local':
                    inpLocalFile.required = true;
                    document.getElementById('cardExternal').classList.remove('show');
                    document.getElementById('cardLocal').classList.add('show');
                    break;

                case 'external':
                    inpLongUrl.required = true;
                    document.getElementById('cardLocal').classList.remove('show');
                    document.getElementById('cardExternal').classList.add('show');
                    break;
            }
        });
    });
    
    // Prevent regular form submission, handle using AJAX
    document.querySelectorAll('form.ajax').forEach((form) => {
        form.addEventListener('submit', function(e){

            var fd = new FormData(e.target);
        
            // Reset the modal dialog
            document.getElementById('modaltitle').textContent = 'Uploading...';
            document.getElementById('uploadmsg').classList.add('show');
            document.getElementById('errormsg').classList.remove('show');
            setProgress(0);

            // Show the modal dialog
            let modal = new bootstrap.Modal(document.querySelector('.modal'));
            modal.show();

            // Append upload file data to form data
            var localfile = document.getElementById('inpLocalFile');
            if (localfile && localfile.files && localfile.files.length > 0) {
                fd.append('localfile', localfile.files[0]);

                // Check client side for max upload size
                const imfs = document.querySelector('input[name="MAX_FILE_SIZE"]');
                if (localfile.files[0].size > parseInt(imfs.value)) {
                    
                    // Show error, hide upload animation and progressbar
                    resetForm();
                    document.getElementById('modaltitle').textContent = 'Error';
                    document.getElementById('uploadmsg').classList.remove('show');
                    document.getElementById('errormsg').classList.add('show');
                    document.getElementById('errormsg').innerHTML = '<p>The file is too big to upload!</p>';
                    
                    e.preventDefault();
                    return false;
                }
            }
            function transferComplete(e) {
                if (req.readyState == req.DONE) {
                    resetForm();
                    const resp = JSON.parse(req.response);

                    if (resp.result == true) {
                        location.reload();
                    } else {
                        document.getElementById('modaltitle').textContent = 'Error';
                        document.getElementById('uploadmsg').classList.remove('show');
                        document.getElementById('errormsg').classList.add('show');
                        document.getElementById('errormsg').innerHTML = '<p>'+resp.error+'</p>';
                    }
                }
            }
            function updateProgress (e) {
                if (e.lengthComputable) {
                    const percentComplete = e.loaded / e.total * 100;
                    setProgress(percentComplete);
                } else {
                    console.log('Oops! Could not compute progress.');
                }
            }
            const req = new XMLHttpRequest();
            req.addEventListener('load', transferComplete);
            req.upload.addEventListener('progress', updateProgress);
            req.open("POST", window.location.href);
            req.send(fd);

            // Abort the upload when modal is closed/hidden
            document.addEventListener('hide.bs.modal', function(ev) {
                req.abort();
                resetForm();
            });
            e.preventDefault();
            return false;
        });
    });
    
    // Loop through all copy-buttons
    document.querySelectorAll('.btn-copy').forEach((b) => {
        b.addEventListener('click', (ev) => {
            let dllink =  b.parentElement.parentElement.querySelector('.dl-link');
            navigator.clipboard.writeText(dllink.href);
        });
    });
    
    // Loop through all download-buttons
    document.querySelectorAll('.btn-download').forEach((b) => {
        b.addEventListener('click', (ev) => {
            let dllink =  b.parentElement.parentElement.querySelector('.dl-link');
            dllink.dispatchEvent(new MouseEvent('click'));
        });
    });
    
    // Loop through all edit-buttons
    document.querySelectorAll('.btn-edit').forEach((b) => {
        b.addEventListener('click', (ev) => {
            let d = JSON.parse(b.parentElement.dataset.obj);
            let ilf = document.getElementById('inpLocalFile');
            let irof = document.getElementById('inpReadOnlyFilename');

            if (d.local) {
                document.getElementById('inpTypeLocal').dispatchEvent(new MouseEvent('click'));
                irof.classList.add('show');
                irof.value = d.fullFilename;
                ilf.classList.remove('show');
                ilf.disabled = true;
            } else {
                document.getElementById('inpTypeExternal').dispatchEvent(new MouseEvent('click'));
                document.getElementById('inpLongUrl').value = d.long;
                irof.classList.remove('show');
                irof.value = '';
                ilf.disabled = false;
                ilf.classList.add('show');
            }
            document.querySelector('input[name="inpType"]:not(:checked)').disabled = true;
            document.getElementById('cmd').value = 'edit';
            document.getElementById('inpId').value = d.id;
            document.getElementById('inpUuid').value = d.uuid;
            document.getElementById('inpValidFrom').value = d.validFrom;
            document.getElementById('inpValidUntil').value = d.validUntil;
        });
    });

    // Loop through all delete-buttons
    document.querySelectorAll('.btn-delete').forEach((b) => {
        b.addEventListener('click', (ev) => {
            let d = JSON.parse(b.parentElement.dataset.obj);
            d.cmd = 'delete';
            d.inpType = 'dummy';
            d._token = document.querySelector('form input[name="_token"]').value;

            fetch(window.location.href, {
                'method': 'POST',
                'body': JSON.stringify(d)
            }).then(resp => {
                if (resp.ok) {
                    b.parentElement.parentElement.parentElement.remove();
                }
            }).catch(er => {
                // Oops, something went wrong
            });
            resetForm();
        });
    });

    // Hookup string filter search box
    let filterUrl = document.getElementById('filterUrl');
    let lis = document.querySelectorAll('.list-group-item');

    if (filterUrl) {
        filterUrl.addEventListener('keyup', (e) => {
            var v = e.target.value.toLowerCase();

            for (let li of lis) {
                var licontent = li.innerHTML;
        
                // If the filter value is present in the li's content...
                if (licontent.toLowerCase().indexOf(v) > -1) {
                    li.classList.remove('d-none');
                } else {
                    li.classList.add('d-none');
                }
            }
        });
    }

    // Hookup password toggle visibility
    document.querySelectorAll('input[type="password"][data-add-toggle="true"]').forEach((inp) => {

        let feedbackDivs = inp.parentElement.querySelectorAll('.valid-feedback, .invalid-feedback');
        let divWrap = document.createElement('div');
        
        divWrap.classList.add('input-group');
        if (feedbackDivs.length > 0) {
            divWrap.classList.add('has-validation');
        }
        inp.parentNode.insertBefore(divWrap, inp);
        divWrap.appendChild(inp);

        let btn = document.createElement('button');
        btn.classList.add('input-group-text', 'btn', 'btn-outline-secondary');
        btn.setAttribute('type', 'button');
        divWrap.appendChild(btn);

        feedbackDivs.forEach((d) => {
            divWrap.appendChild(d);
        });

        let spn = document.createElement('span');
        spn.classList.add('fa', 'fa-eye-slash');
        spn.ariaLabel = 'Password is masked; press to toggle';
        spn.setAttribute('aria-label', spn.ariaLabel);
        btn.appendChild(spn);

        btn.addEventListener('click', (e) => {
            let type = inp.getAttribute('type') === 'password' ? 'text' : 'password';   
            inp.setAttribute('type', type);
        
            switch (type) {

                case 'text':
                    spn.classList.remove('fa-eye-slash');
                    spn.classList.add('fa-eye');
                    spn.ariaLabel = 'Password is readable; press to toggle';
                    spn.setAttribute('aria-label', spn.ariaLabel);
                    break;

                case 'password':
                    spn.classList.remove('fa-eye');
                    spn.classList.add('fa-eye-slash');
                    spn.ariaLabel = 'Password is masked; press to toggle';
                    spn.setAttribute('aria-label', spn.ariaLabel);
                    break;
            }
        });
    });
});
