

function ytvid(idSpan, idVideo)
{
    if(document.getElementById(idVideo).style.display == 'none') {

        var link = document.getElementById(idSpan).innerHTML.replace("youtu.be", "www.youtube.com/embed");
        link = link.replace("watch?v=", "embed/");
        document.getElementById(idVideo).src = link;
        document.getElementById(idVideo).style.display = 'block';
    }
    else {
        document.getElementById(idVideo).removeAttribute('src');
        document.getElementById(idVideo).style.display = 'none';
    }
}
/*
function ytvid(randomID, randomID2)
{
    if(document.getElementById(randomID).className == "hidevideo") {
        document.getElementById(randomID).className = "showvideo";
        document.getElementById(randomID).innerHTML = document.getElementById(randomID2).innerHTML;
    }
    else {
        document.getElementById(randomID).className = "hidevideo";
        document.getElementById(randomID).innerHTML = "";
    }
}
*/
function postPreview(event, num) {
    var x = event.clientX + 10;
    var y = event.clientY - 50;
    document.getElementById('preview').style.left = x;
    document.getElementById('preview').style.top = y;
    var content = document.getElementById(num).innerHTML;
    document.getElementById('preview').innerHTML = content;
    document.getElementById('preview').style.display = 'block';
}
function hidePostPreview() {
    document.getElementById('preview').innerHTML = '';
    document.getElementById('preview').style.display = 'none';
}  

function showForm() {
    document.getElementById("form").style.display = "inline-block";
    document.getElementById("showForm").style.display = "none";
}

function showLogin() {
    document.getElementById("login").style.display = "inline-block";
    document.getElementById("showLogin").style.display = "none";
}

function showButton(x) {
    document.getElementById(x).style.display = "inline-block";
    }

function resizepic(id) {

    var pic = document.getElementById(id).src;
    if(pic.includes("thumbnails")) {
        pic = pic.replace("thumbnails", "uploads");
        document.getElementById(id).src = pic;
    }
    else {
        pic = pic.replace("uploads", "thumbnails");
        document.getElementById(id).src = pic;
    }
}

function showRules() {
    document.getElementById('rules').style.display = 'block';
}
/*
function expand(num) {
    var x = 'replies' + num;
    alert(x);
}
*/
function expand(num) {
    var buttonId = 'expandButton' + num;

    if(document.getElementById(buttonId).innerHTML == '▼') {
        document.getElementById(buttonId).innerHTML = '▲';
        var x = 'replies' + num; 
        var y = "/expand.php?num=" + num;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById(x).innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", y, true);
        xhttp.send();
    }
    else {
        document.getElementById(buttonId).innerHTML = '▼';
        var x = 'replies' + num; 
        var y = "/unexpand.php?num=" + num;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById(x).innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", y, true);
        xhttp.send();
    }
}



$(document).ready(function(){

    $(".smallpic").hover(function(event){
        var idx = "#" + this.id;
        $(idx).toggleClass("smallpic largepic");
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
        $(".replyWindow").css("display", "inline-block");
    });
        $(".close").click(function(event){
            $(".replyWindow").css("display", "none");
    });

    $(".register").click(function(event){
        $(".registerWindow").css("display", "block");
    });

    $( function() { $( "#draggable" ).draggable(); } );
});