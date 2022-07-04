$(window).on("load",function (){
    checkSession();
    openAccount();
});

function openAccount(){
    let request = new Request("api.php?do=loginPast",{
        method: 'POST',
    });

    fetch(request)
        .then(response => response.json())
        .then(data => {

            if(!data.error){
                addDataToHtml(data);
            }
            else{
                $("#user").html("Problém s načítaním");
            }
        });
}

function addDataToHtml(data){
    $("#name-loginPast").html(data.name.name + " " + data.name.surname);
    for(let i = 0; i< data.logins.length; i++){
        let tr = document.createElement("tr");
        let td = document.createElement("td");
        $(td).html(data.logins[i].timestamp);
        $(tr).append(td);
        $("#logs").append(tr);
    }

    for(let i = 0; i< data.statisticOfLogs.length; i++){
        let tr = document.createElement("tr");
        let td1 = document.createElement("td");
        $(td1).html(data.statisticOfLogs[i].type);
        let td2 = document.createElement("td");
        $(td2).html(data.statisticOfLogs[i].counts);

        $(tr).append(td1,td2);
        $("#statistic").append(tr);
    }
}
function checkSession(){
    fetch('api.php?do=session')
        .then(response => response.json())
        .then(data => {
            if (!data.isSet)
                window.location.replace("index.html");
        });
}
