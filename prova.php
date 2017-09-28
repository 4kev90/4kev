 <!DOCTYPE html>
<html>
  
  
<script>
  function loadDoc() {
    alert('ok');
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     document.getElementById("demo").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "prova2.php", true);
  xhttp.send();
}
</script>
  
  
<body>

  test

<div id="demo">
  <h2>Let AJAX change this text</h2>
  <button type="button" onclick="loadDoc()">Change Content</button>
</div>

test

</body>
</html> 
