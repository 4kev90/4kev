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

function preview(event, num) {
    var x = event.clientX + 10;
    var y = event.clientY - 50;
    document.getElementById('preview').style.left = x;
    document.getElementById('preview').style.top = y;

    var z = '/preview.php?num=' + num;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById('preview').innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", z, true);
    xhttp.send();

    document.getElementById('preview').style.display = 'block';
}

function hidePostPreview() {
    document.getElementById('preview').innerHTML = '';
    document.getElementById('preview').style.display = 'none';
}

function showPostWindow() {
    document.getElementById("postWindow").style.display = "inline-block";
}

function showReplyWindow() {
    document.getElementById("replyWindow").style.display = "inline-block";
}

function showRegisterWindow() {
    document.getElementById("registerWindow").style.display = "inline-block";
    document.getElementById("loginWindow").style.display = "none";

}

function showLoginWindow() {
    document.getElementById("loginWindow").style.display = "inline-block";
    document.getElementById("registerWindow").style.display = "none";
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
    if (pic.indexOf("thumbnails") !== -1) {        
        document.getElementById(id).style.filter = 'invert(100%)';
        pic = pic.replace("thumbnails", "uploads");
        document.getElementById(id).src = pic;
        document.getElementById(id).onload = function() { document.getElementById(id).style.filter = 'invert(0%)'; };
        document.getElementById(id).classList.add('expanded_image');
        document.getElementById(id).classList.remove('thumbnail');
    }
        
    else {
        pic = pic.replace("uploads", "thumbnails");
        document.getElementById(id).src = pic;
        document.getElementById(id).classList.add('thumbnail');
        document.getElementById(id).classList.remove('expanded_image');
    }
}

function resizeUrl(id) {
    if(document.getElementById(id).className == "smallUrl") 
        document.getElementById(id).className = "bigUrl";
    else
        document.getElementById(id).className = "smallUrl";
}

function showStatistics() {
    document.getElementById('statistics').style.display = 'block';
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

function formAction(ID) {
    var x = '/newPost.php?op=' + ID;
    document.getElementById('formAction').action = x;
}


$(document).ready(function(){

    $(".redArrow").click(function(event){
        if($(this).html() == "▶")
            $(this).html("▼");
        else
            $(this).html("▶");
    });

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

    $(document).on('click', '.quickReply', function(event){
        var str1 = $("#linky").val() + ">>" + $(this).html() + "\n";
        $("#linky").val(str1);
        $("#replyWindow").css("display", "inline-block");
    });

    $(document).on('click', '.redArrow', function(event) {
        var x = event.clientX + 10;
        var y = event.clientY;
        $("#dropDown").css("left", x);
        $("#dropDown").css("top", y);
        $("#dropDown").css("display", "inline-block");
    });


    $(".quickReply2").click(function(event){
        $(this).html() = "test";
    });

    $("#registerButton").click(function(event){
        $("#JS_enabled").html("enabled");
    });

    $("#closeReplyWindow").click(function(event){
        $("#replyWindow").css("display", "none");
    });

    $("#closePostWindow").click(function(event){
        $("#postWindow").css("display", "none");
    });

    $(".register").click(function(event){
        $(".registerWindow").css("display", "block");
    });

var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ? true : false;
    if(!isMobile) {
        $( function() { $( ".draggable" ).draggable(); } );
        $('.draggable').draggable();
    }   

});