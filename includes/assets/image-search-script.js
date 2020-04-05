
document.addEventListener("DOMContentLoaded", ccimage_modal);

function loadIframe(){
	let srcUrl = 'https://ccsearch.creativecommons.org/search?q=';
	document.getElementById('cc-image-iframe').src=srcUrl;
	
	//on iframe loaded display loader none;
	document.getElementById('cc-image-iframe').onload = function(){
		document.getElementById('cc-image-iframe-loader-gif').style.display = "none";
	};
}

function ccimage_modal(){

// Get the modal
var modal = document.getElementById("CC-Image-Modal");

// Get the button that opens the modal
var btn = document.getElementById("open-ccimage-modal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("ccmodal-close")[0];

// When the user clicks the button, open the modal 


btn.onclick = function() {
  modal.style.display = "block";
  
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

// display api-data in model
/*let searchBtn = document.getElementById("cc-search-btn");
searchBtn.onclick = function() {  loadapidata();}
function loadapidata() {
	
	let key = document.getElementById("cc-search-key").value;
	
	fetch('https://api.creativecommons.engineering/v1/images?q='+ key )
  .then((response) => {
    return response.json();
  })
  .then((data) => {
	  var childdiv = document.createElement('p');
    for(i=0; i<=data.results.length;i++){
		childdiv.innerHTML = data.results[i].title;
		document.getElementById("cc-api-data").appendChild(childdiv);
	}
  });
  
} */

//load iframe after dom loaded completely
setTimeout(loadIframe, 2000);

}