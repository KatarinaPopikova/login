$(window).on("load",function (){
    checkSession();
    openAccount();
});

function openAccount(){
    fetch("api.php?do=account")
        .then(response => response.json())
        .then(data => {

            if(!data.error){
               userInfo(data.user);
            }
            else{
                $("#user").html("Problém s načítaním");
            }
        });
}


function userInfo(user){
    $("#userAccess").html(user.name + " " + user.surname);
    $("#emailAccess").html("<b>E-mail: </b>" + user.email);
    $("#typeAccess").html("<b>Type: </b>" +user.type);
}

function logOut(){
    fetch('api.php?do=logOut')
        .then(response => response.json())
        .then(data => {
            if (!data.error){
                window.location.replace('index.html')
            }
        });
}

function checkSession(){
    fetch('api.php?do=session')
        .then(response => response.json())
        .then(data => {
            if (!data.isSet)
                window.location.replace("index.html");
        });
}

