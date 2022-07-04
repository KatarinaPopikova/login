$(window).on("load",function (){
    $("input").on("input",function (){
        $("input").removeClass("is-invalid");
    })
    isLogin();
    hideLdap();
    getGoogleLink();
});

function loginUser(){

    let formular = $("#control-code-from-qr").get(0);
    let formData = new FormData(formular);
    formData.append("email",$("#email").val());


    let request = new Request("api.php?do=login",{
        method: 'POST',
        body: formData,
    });

    fetch(request)
        .then(response => response.json())
        .then(data => {

            if(!data.error){
             window.location.replace("user.html");
            }
            else{
                if(data.message === "wrong-code"){
                    $("#qr").addClass("is-invalid");
                }


            }
        });
}


function checkLogin(){
    let formular = $("#login-formular").get(0);

    let request = new Request("api.php?do=check-login",{
        method: 'POST',
        body: new FormData(formular),
    });

    fetch(request)
        .then(response => response.json())
        .then(data => {

            if(!data.error){
                if(data.isCorrect){
                    $('#login-modal').modal({
                        keyboard: false
                    });
                }
                else {
                    $("#password").addClass("is-invalid");

                }

            }
            else{
                if(data.message === "noEmail"){
                    $("#email").addClass("is-invalid");
                }


            }
        });
}

function loginLdap(){
    if($(".ldap").css("display")==="none")
        showLdap();

    else
        hideLdap();

}
function showLdap(){
    $(".not-ldap").css("display", "none");
    $(".ldap").css("display", "block");
    $("#ldap-div2").css("opacity", 1);
}

function hideLdap(){
    $(".not-ldap").css("display", "block");
    $(".ldap").css("display", "none");
    $("#ldap-div2").css("opacity", 0.5);

}
function getGoogleLink(){
    fetch("api.php?do=getGoogleLink")
        .then(response => response.json())
        .then(data => {

            if(!data.error){
                setGoogleLink(data.googleLink)
            }
            else{
                $("#email").addClass("is-invalid");
                $("#password").addClass("is-invalid");
            }

        });
}

function setGoogleLink(googleLink){
    $("#google").on('click',function (){
        window.location.replace(googleLink);
    });
}

function isLogin(){
    fetch('api.php?do=checkLogin')
        .then(response => response.json())
        .then(data => {
            if (data.login)
                window.location.replace("user.html");
        });
}

function loginWithLdap(){
    let formular = $("#login-formular").get(0);

    let request = new Request("ldap.php",{
        method: 'POST',
        body: new FormData(formular),
    });

    fetch(request)
        .then(response => response.json())
        .then(data => {

            if(!data.error){
                window.location.replace("user.html");
            }
            else{
                let feedback = $(".invalid-feedback");
                if(data.message === "AnotherType"){
                    $("#email").addClass("is-invalid");
                    feedback.html("Email existuje s iným typom prihlásenia.");

                }
                else if (data.message === "notStuba"){
                    $("#email").addClass("is-invalid");
                    feedback.html("Emailová adresa nie je od stuba.sk");}
                else {
                    feedback.html("Nesprávne heslo");
                    $("#password").addClass("is-invalid");
                }
            }
        });
}