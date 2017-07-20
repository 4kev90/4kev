function ytvid(randomID)
{
    if(document.getElementById(randomID).className == "hidevideo")
        document.getElementById(randomID).className = "showvideo";
    else
        document.getElementById(randomID).className = "hidevideo";
}

function showForm() {
    document.getElementById("form").style.display = "block";
    document.getElementById("showForm").style.display = "none";
}

function showLogin() {
    document.getElementById("login").style.display = "block";
    document.getElementById("showLogin").style.display = "none";
}

function showButton(x) {
    document.getElementById(x).style.display = "inline-block";
    }



$(document).ready(function(){

    $(".smallpic").hover(function(event){
        var idx = "#" + this.id;
        $(idx).toggleClass("smallpic largepic");
    });
        

    $(".postlink").hover(function(event){
        var x = event.clientX + 10;
        var y = event.clientY;
        var idx = "#" + this.id;
        var plink = $(idx).html();
        $(".preview").css("left", x);
        $(".preview").css("top", y);
        $(".preview").html(plink);
        $(".preview").show();
    }, function(event){
        var x = event.clientX + 10;
        var y = event.clientY;
        var idx = "#" + this.id;
        var plink = $(idx).html();
        $(".preview").css("left", x);
        $(".preview").css("top", y);
        $(".preview").html(plink);
        $(".preview").hide();
    });

     $(".embed").click(function(event){
        if($(this).html() == "embed")
            $(this).html("remove");
        else
            $(this).html("embed");
    });

    $(".quickReply").click(function(event){
        var x = event.clientX + 10;
        var y = event.clientY;
        $(".replyWindow").css("left", x);
        $(".replyWindow").css("top", y);
        var str1 = $("#linky").val() + ">>" + $(this).html() + "\n";
        $("#linky").val(str1);
        $(".replyWindow").css("display", "block");
    });
        $(".close").click(function(event){
            $(".replyWindow").css("display", "none");
    });

    $(".register").click(function(event){
        $(".registerWindow").css("display", "block");
    });

    $( function() { $( "#draggable" ).draggable(); } );
});