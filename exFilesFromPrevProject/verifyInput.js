
//<script>

// The event handler function to compute the total cost
//function to determine if a field is blank

function checkPassword(password){

	var  re = /[0-9]/;
    if( ! re.test(password.value)) {
		alert("Password must contain at least one digit");
		return false;
    }
	re = /[A-Z]/;
    if( ! re.test(password.value)) {
		alert("Password must contain at least one uppercase letter");
		return false;
    }
	re = /[a-z]/;
    if( ! re.test(password.value)) {
		alert("Password must contain at least one lowercase letter");
		return false;
    }
	if( password.value.length < 6) {
		alert("Password must have at least 6 characters");
		return false;
    }
    return true;

}

function isBlank(inputField){
    if(inputField.type=="checkbox"){
		if(inputField.checked)
			return false;
		return true;
    }
    if (inputField.value==""){
		return true;
    }
    return false;
}

//function to highlight an error through colour by adding css attributes tot he div passed in
function makeRed(inputDiv){
   	inputDiv.style.backgroundColor="#AA0000";
	//inputDiv.parentNode.style.backgroundColor="#AA0000";
	inputDiv.parentNode.style.color="#FFFFFF";
}

//remove all error styles from the div passed in
function makeClean(inputDiv){
	inputDiv.parentNode.style.backgroundColor="#FFFFFF";
	inputDiv.parentNode.style.color="#000000";
}

window.onload = function(){
	//the main function must occur after the page is loaded, hence being inside the window.onload event handler.
	$('#signupForm').submit(function(event){
		var myForm = document.getElementById("signupForm");
		if(myForm != null){
			var password = document.getElementById("password");
			if(checkPassword(password)){
				makeClean(password);
			}
			else{
				makeRed(password);
				event.preventDefault();
			}
		}

		if(myForm != null){
			var checkList = document.getElementsByClassName("required");
			for( x in checkList ){
				if(x.id == "password"){
					continue;
				}
				if(isBlank(x)){
					alert("Problem");
					console.log(x);
					event.preventDefault();
					event.stopImmediatPropagation()
				}
			}
		}
	});
}
//</script>
