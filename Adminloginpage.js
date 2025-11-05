function mysigninbutton() {
  var x = document.getElementById("adminname").value;
  var pw = document.getElementById("adminpassword").value;
  if (x == "") {
    alert("Name must be filled out");
    return false;
  }
  //check empty password field
  else if (pw == "") {
    alert("password required");
    return false;
  }
}

function mycancelbutton(){
  window.location.href="localhost/MINIPROJECT1/Frontpage.html";
}
