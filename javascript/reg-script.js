$(window).on("load",function (){
    getTwoFaQrCode();

});

function regUser(){
    let formular = $("#registration-formular").get(0);
    let formData = new FormData(formular);
    formData.append("qr",$("#qr").val());

    let request = new Request("api.php?do=registration",{
        method: 'POST',
        body: formData,
    });

    if(checkFormValidation(formular)) {

        fetch(request)
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    window.location.replace("index.html");
                } else {
                    if (data.message === 'wrong-code')
                        $("#qr").addClass("is-invalid");


                }

            });
    }
}
function checkEmail(){
    let formular = $("#registration-formular").get(0);
    let request = new Request("api.php?do=checkEmail",{
        method: 'POST',
        body: new FormData(formular),
    });

    if(checkFormValidation(formular)) {
        fetch(request)
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    if (data.isUser === false) {
                        $('#two-fa-modal').modal({
                            keyboard: false
                        });
                    } else {
                        $("#email").addClass("is-invalid");
                    }
                } else {

                }

            });
    }
}

function getTwoFaQrCode(){
    fetch("api.php?do=getQR")
        .then(response => response.json())
        .then(data => {
            if(!data.error)
                $("#qr-code-img").attr("src",data.qrUrl);
            else
            {

            }

        });

}

function checkPassword(){
    let password1 = $("#password");
    let password2 = $("#password-control");
    if(password1.val() !== password2.val())
        password2.get(0).setCustomValidity("Heslá sa nezhodujú");
    else
        password2.get(0).setCustomValidity("");

}

function checkFormValidation(form){
    let inputs = $(form).find("input");
    for (let i = 0; i < inputs.length; i++) {
        if (!inputs.get(i).checkValidity())
            return false;
    }
    return true;

}

